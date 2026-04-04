<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\SupportAutoReply;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SystemController extends Controller
{
    use AjaxResponses;

    private static function pidFilePath(string $name): string
    {
        return storage_path("framework/{$name}.pid");
    }

    private static function isProcessRunning(string $name): bool
    {
        $pidFile = static::pidFilePath($name);
        if (!file_exists($pidFile)) {
            return false;
        }
        $pid = (int) trim(file_get_contents($pidFile));
        if ($pid <= 0) {
            return false;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec("tasklist /FI \"PID eq {$pid}\" /NH 2>NUL", $output);
            foreach ($output as $line) {
                if (strpos($line, (string) $pid) !== false && stripos($line, 'INFO:') === false) {
                    return true;
                }
            }
            return false;
        }

        return posix_kill($pid, 0);
    }

    private static function getProcessPid(string $name): ?int
    {
        $pidFile = static::pidFilePath($name);
        if (!file_exists($pidFile)) {
            return null;
        }
        $pid = (int) trim(file_get_contents($pidFile));
        return $pid > 0 ? $pid : null;
    }

    public function settings(Request $request)
    {
        $urls = config('access-controls.bank_urls_string');
        $urls = explode(',', $urls);

        $test_product_config = config('test_product');

        $test_product = [];
        $test_product_price = [];
        if ($test_product_config && $test_product_config['product_id']) {
            $test_product = Product::find($test_product_config['product_id']);
            $test_product_price = Price::find($test_product_config['price_id']);
        }

        $localtonetHttpVerify = (bool) config('services.localtonet.http_verify');

        $systemStatus = $this->getSystemStatusData();

        return view('admin.pages.system.settings', compact('urls', 'test_product', 'test_product_price', 'localtonetHttpVerify', 'systemStatus'));
    }

    public function systemStatusAjax()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSystemStatusData(),
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
    }

    public function startProcess(Request $request)
    {
        $type = $request->input('type');
        $php = PHP_BINARY ?: 'php';
        $artisan = base_path('artisan');

        $allowed = ['scheduler', 'queue'];
        if (!in_array($type, $allowed)) {
            return $this->errorResponse('Geçersiz işlem türü.');
        }

        if (static::isProcessRunning($type)) {
            return $this->errorResponse(ucfirst($type) . ' zaten çalışıyor.');
        }

        $logFile = storage_path("logs/{$type}-worker.log");

        if ($type === 'scheduler') {
            $cmd = "\"{$php}\" \"{$artisan}\" schedule:work";
        } else {
            $cmd = "\"{$php}\" \"{$artisan}\" queue:work --stop-when-empty --timeout=60";
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $baseDir = base_path();
            $batFile = storage_path("framework/{$type}-worker.bat");
            $batContent = "@echo off\r\ncd /d \"{$baseDir}\"\r\n{$cmd} > \"{$logFile}\" 2>&1\r\n";
            file_put_contents($batFile, $batContent);

            $desc = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ];
            $process = proc_open("start /B \"\" \"{$batFile}\"", $desc, $pipes, $baseDir);
            if (is_resource($process)) {
                fclose($pipes[0]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
            }
            sleep(3);

            $pid = null;
            $search = $type === 'scheduler' ? 'schedule:work' : 'queue:work';
            $output = [];
            exec("wmic process where \"CommandLine like '%{$search}%' and not CommandLine like '%wmic%'\" get ProcessId /format:list 2>NUL", $output);
            foreach ($output as $line) {
                if (preg_match('/ProcessId=(\d+)/', $line, $m)) {
                    $pid = (int) $m[1];
                }
            }

            if (!$pid) {
                exec("wmic process where \"CommandLine like '%artisan%' and CommandLine like '%{$search}%'\" get ProcessId /format:list 2>NUL", $output);
                foreach ($output as $line) {
                    if (preg_match('/ProcessId=(\d+)/', $line, $m)) {
                        $pid = (int) $m[1];
                    }
                }
            }

            if ($pid) {
                file_put_contents(static::pidFilePath($type), $pid);
            }
        } else {
            $fullCmd = "{$cmd} > \"{$logFile}\" 2>&1 & echo $!";
            $pid = trim(shell_exec($fullCmd));
            if ($pid) {
                file_put_contents(static::pidFilePath($type), $pid);
            }
        }

        $label = $type === 'scheduler' ? 'Zamanlayıcı' : 'Kuyruk İşçisi';
        return $this->successResponse("{$label} başarıyla başlatıldı.");
    }

    public function stopProcess(Request $request)
    {
        $type = $request->input('type');

        $allowed = ['scheduler', 'queue'];
        if (!in_array($type, $allowed)) {
            return $this->errorResponse('Geçersiz işlem türü.');
        }

        $pid = static::getProcessPid($type);
        if (!$pid) {
            @unlink(static::pidFilePath($type));
            return $this->errorResponse('Çalışan işlem bulunamadı.');
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("taskkill /PID {$pid} /F /T 2>NUL");
        } else {
            posix_kill($pid, 15);
        }

        @unlink(static::pidFilePath($type));

        $label = $type === 'scheduler' ? 'Zamanlayıcı' : 'Kuyruk İşçisi';
        return $this->successResponse("{$label} durduruldu.");
    }

    private function getSystemStatusData(): array
    {
        $schedulerRunning = static::isProcessRunning('scheduler');
        $schedulerLastRun = null;

        $scheduleFiles = glob(storage_path('framework/schedule-*'));
        if (!empty($scheduleFiles)) {
            $latestFile = end($scheduleFiles);
            $mtime = filemtime($latestFile);
            if ($mtime && (time() - $mtime) < 120) {
                $schedulerRunning = true;
            }
            $schedulerLastRun = $mtime ? date('d.m.Y H:i:s', $mtime) : null;
        }

        $queueWorkerRunning = static::isProcessRunning('queue');

        $queuePending = 0;
        try {
            if (\Schema::hasTable('jobs')) {
                $queuePending = DB::table('jobs')->count();
            }
        } catch (\Throwable $e) {}

        $queueFailed = 0;
        try {
            if (\Schema::hasTable('failed_jobs')) {
                $queueFailed = DB::table('failed_jobs')->count();
            }
        } catch (\Throwable $e) {}

        $deliveryQueued = Order::where('delivery_status', 'QUEUED')->count();
        $deliveryBeingDelivered = Order::where('delivery_status', 'BEING_DELIVERED')->count();
        $deliveryNotDelivered = Order::where('delivery_status', 'NOT_DELIVERED')->where('status', '!=', 'CANCELLED')->count();
        $totalDelivered = Order::where('delivery_status', 'DELIVERED')->count();

        $autoReplyTotal = SupportAutoReply::withTrashed()->count();
        $autoReplyActive = SupportAutoReply::where('is_active', true)->count();
        $autoReplyInactive = SupportAutoReply::where('is_active', false)->count();

        $scheduledCommands = [
            ['command' => 'app:deliver-localtonet-orders', 'schedule' => 'Her dakika', 'description' => 'Localtonet siparişlerini teslim et'],
            ['command' => 'app:per-minute-jobs', 'schedule' => 'Her dakika', 'description' => 'Dakikalık arka plan görevleri'],
            ['command' => 'app:renew-orders', 'schedule' => 'Her gün 10:00', 'description' => 'Sipariş yenileme kontrolleri'],
            ['command' => 'app:invoices-with-upcoming-due-dates', 'schedule' => 'Her gün 10:00', 'description' => 'Yaklaşan son ödeme tarihi hatırlatmaları'],
            ['command' => 'app:stop-service-on-unpaid-renew-invoices', 'schedule' => 'Her gün 02:00', 'description' => 'Ödenmemiş yenileme faturalarında hizmeti durdur'],
            ['command' => 'app:stop-test-product-orders', 'schedule' => 'Her 10 dakika', 'description' => 'Test ürünü siparişlerini durdur'],
        ];

        $autoSystems = [
            [
                'name' => 'Otomatik Teslimat (Localtonet)',
                'type' => 'delivery',
                'icon' => 'fa-truck',
                'color' => 'primary',
                'description' => 'Localtonet siparişlerini otomatik teslim eder',
                'running' => $schedulerRunning,
                'stats' => [
                    'Bekleyen (QUEUED)' => $deliveryQueued,
                    'Teslim Ediliyor' => $deliveryBeingDelivered,
                    'Teslim Edilmedi' => $deliveryNotDelivered,
                    'Toplam Teslim' => $totalDelivered,
                ],
            ],
            [
                'name' => 'Otomatik Teslimat (PProxy)',
                'type' => 'delivery',
                'icon' => 'fa-globe',
                'color' => 'info',
                'description' => 'PProxy siparişlerini onay ile anında teslim eder',
                'running' => true,
                'stats' => [
                    'PProxy Bekleyen' => Order::whereHas('product', fn($q) => $q->where('delivery_type', 'PPROXY'))->where('delivery_status', 'QUEUED')->count(),
                    'PProxy Teslim' => Order::whereHas('product', fn($q) => $q->where('delivery_type', 'PPROXY'))->where('delivery_status', 'DELIVERED')->count(),
                ],
            ],
            [
                'name' => 'Otomatik Teslimat (PProxyU)',
                'type' => 'delivery',
                'icon' => 'fa-network-wired',
                'color' => 'success',
                'description' => 'PProxyU siparişlerini havuzdan anında teslim eder',
                'running' => true,
                'stats' => [
                    'PProxyU Bekleyen' => Order::whereJsonContains('product_data->delivery_type', 'PPROXYU')->whereIn('delivery_status', ['QUEUED', 'BEING_DELIVERED'])->count(),
                    'PProxyU Teslim' => Order::whereJsonContains('product_data->delivery_type', 'PPROXYU')->where('delivery_status', 'DELIVERED')->count(),
                    'Havuz Aktif Proxy' => \App\Models\PProxyUPool::where('is_active', true)->count(),
                ],
            ],
            [
                'name' => 'Otomatik Destek Yanıtları',
                'type' => 'auto_reply',
                'icon' => 'fa-robot',
                'color' => 'warning',
                'description' => 'Destek taleplerine otomatik yanıt gönderir',
                'running' => $autoReplyActive > 0,
                'stats' => [
                    'Aktif Kural' => $autoReplyActive,
                    'Pasif Kural' => $autoReplyInactive,
                    'Toplam Kural' => $autoReplyTotal,
                ],
            ],
            [
                'name' => 'Sipariş Yenileme',
                'type' => 'renewal',
                'icon' => 'fa-sync-alt',
                'color' => 'danger',
                'description' => 'Süresi dolan siparişleri yeniler ve fatura oluşturur',
                'running' => $schedulerRunning,
                'stats' => [],
            ],
            [
                'name' => 'Fatura Hatırlatma',
                'type' => 'invoice',
                'icon' => 'fa-file-invoice',
                'color' => 'dark',
                'description' => 'Yaklaşan son ödeme tarihli faturaları bildirir',
                'running' => $schedulerRunning,
                'stats' => [],
            ],
        ];

        return [
            'scheduler_running' => $schedulerRunning,
            'scheduler_last_run' => $schedulerLastRun,
            'queue_worker_running' => $queueWorkerRunning,
            'queue_pending' => $queuePending,
            'queue_failed' => $queueFailed,
            'auto_systems' => $autoSystems,
            'scheduled_commands' => $scheduledCommands,
        ];
    }

    public function updateSettings(Request $request)
    {
        $urls = $request->input('urls');
        if ($urls) {
            $newContent = "<?php\n\nreturn [\n    'bank_urls_string' => '" . str_replace("\r\n", ',', $urls) . "',\n];\n";
            $configPath = config_path('access-controls.php');
            File::put($configPath, $newContent);
        }

        $test_product = $request->test_product;

        if (!isset($test_product['status'])) {
            $test_product['status'] = 0;
        }
        file_put_contents(config_path('test_product.php'), '<?php return ' . var_export($test_product, true) . ';');

        $localtonetHttpVerify = $request->boolean('localtonet_http_verify');
        file_put_contents(
            config_path('localtonet_settings.php'),
            '<?php return ' . var_export(['http_verify' => $localtonetHttpVerify], true) . ';'
        );

        Artisan::call('config:clear');

        return redirect()->route('admin.settings')->with('form_success', 'Değişiklikler başarıyla kaydedildi.');
    }
}
