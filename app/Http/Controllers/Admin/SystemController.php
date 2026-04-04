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

    private static function isSchedulerRunning(): bool
    {
        $heartbeat = storage_path('framework/scheduler-heartbeat');
        if (file_exists($heartbeat)) {
            $lastRun = (int) file_get_contents($heartbeat);
            if ($lastRun > 0 && (time() - $lastRun) < 180) {
                return true;
            }
        }

        $scheduleFiles = glob(storage_path('framework/schedule-*'));
        if (!empty($scheduleFiles)) {
            $latestFile = end($scheduleFiles);
            $mtime = filemtime($latestFile);
            if ($mtime && (time() - $mtime) < 180) {
                return true;
            }
        }

        return false;
    }

    private static function getSchedulerLastRun(): ?string
    {
        $heartbeat = storage_path('framework/scheduler-heartbeat');
        if (file_exists($heartbeat)) {
            $ts = (int) file_get_contents($heartbeat);
            if ($ts > 0) {
                return date('d.m.Y H:i:s', $ts);
            }
        }

        $scheduleFiles = glob(storage_path('framework/schedule-*'));
        if (!empty($scheduleFiles)) {
            $latestFile = end($scheduleFiles);
            $mtime = filemtime($latestFile);
            if ($mtime) {
                return date('d.m.Y H:i:s', $mtime);
            }
        }

        return null;
    }

    private static function isQueueWorkerRunning(): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $output = [];
            exec('wmic process where "CommandLine like \'%queue:work%\' and not CommandLine like \'%wmic%\'" get ProcessId /format:list 2>NUL', $output);
            foreach ($output as $line) {
                if (preg_match('/ProcessId=(\d+)/', $line, $m)) {
                    return true;
                }
            }
            return false;
        }

        $output = [];
        exec("pgrep -f 'queue:work' 2>/dev/null", $output);
        if (!empty($output)) {
            return true;
        }

        $result = @shell_exec("supervisorctl status proxynetic-worker:* 2>/dev/null");
        if ($result && stripos($result, 'RUNNING') !== false) {
            return true;
        }

        return false;
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

        $allowed = ['scheduler', 'queue'];
        if (!in_array($type, $allowed)) {
            return $this->errorResponse('Geçersiz işlem türü.');
        }

        if ($type === 'scheduler') {
            if (static::isSchedulerRunning()) {
                return $this->successResponse('Zamanlayıcı zaten çalışıyor (cron aktif).');
            }

            $php = PHP_BINARY ?: '/opt/plesk/php/8.3/bin/php';
            $artisan = base_path('artisan');

            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $logFile = storage_path('logs/scheduler-worker.log');
                $baseDir = base_path();
                $batFile = storage_path('framework/scheduler-worker.bat');
                $cmd = "\"{$php}\" \"{$artisan}\" schedule:work";
                $batContent = "@echo off\r\ncd /d \"{$baseDir}\"\r\n{$cmd} > \"{$logFile}\" 2>&1\r\n";
                file_put_contents($batFile, $batContent);
                $desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                $process = proc_open("start /B \"\" \"{$batFile}\"", $desc, $pipes, $baseDir);
                if (is_resource($process)) {
                    fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
                    proc_close($process);
                }
            } else {
                try {
                    Artisan::call('schedule:run');
                    file_put_contents(storage_path('framework/scheduler-heartbeat'), time());
                } catch (\Throwable $e) {}
            }

            return $this->successResponse('Zamanlayıcı tetiklendi. Cron job aktifse her dakika otomatik çalışır.');
        }

        if ($type === 'queue') {
            if (static::isQueueWorkerRunning()) {
                return $this->successResponse('Kuyruk İşçisi zaten çalışıyor.');
            }

            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $result = @shell_exec('supervisorctl start proxynetic-worker:* 2>&1');
                if ($result && stripos($result, 'ERROR') === false) {
                    return $this->successResponse('Kuyruk İşçisi başlatıldı (supervisor).');
                }

                $php = PHP_BINARY ?: '/opt/plesk/php/8.3/bin/php';
                $artisan = base_path('artisan');
                $logFile = storage_path('logs/queue-worker.log');
                $cmd = "nohup {$php} {$artisan} queue:work database --sleep=3 --tries=3 --max-time=3600 >> {$logFile} 2>&1 &";
                @shell_exec($cmd);
                return $this->successResponse('Kuyruk İşçisi başlatıldı.');
            } else {
                $php = PHP_BINARY ?: 'php';
                $artisan = base_path('artisan');
                $logFile = storage_path('logs/queue-worker.log');
                $baseDir = base_path();
                $batFile = storage_path('framework/queue-worker.bat');
                $cmd = "\"{$php}\" \"{$artisan}\" queue:work database --sleep=3 --tries=3 --max-time=3600";
                $batContent = "@echo off\r\ncd /d \"{$baseDir}\"\r\n{$cmd} > \"{$logFile}\" 2>&1\r\n";
                file_put_contents($batFile, $batContent);
                $desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
                $process = proc_open("start /B \"\" \"{$batFile}\"", $desc, $pipes, $baseDir);
                if (is_resource($process)) {
                    fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
                    proc_close($process);
                }
                return $this->successResponse('Kuyruk İşçisi başlatıldı.');
            }
        }

        return $this->errorResponse('Bilinmeyen işlem.');
    }

    public function stopProcess(Request $request)
    {
        $type = $request->input('type');

        $allowed = ['scheduler', 'queue'];
        if (!in_array($type, $allowed)) {
            return $this->errorResponse('Geçersiz işlem türü.');
        }

        if ($type === 'scheduler') {
            @unlink(storage_path('framework/scheduler-heartbeat'));
            return $this->successResponse('Zamanlayıcı durumu sıfırlandı. Kalıcı durdurmak için Plesk cron job\'ı devre dışı bırakın.');
        }

        if ($type === 'queue') {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $result = @shell_exec('supervisorctl stop proxynetic-worker:* 2>&1');
                if ($result && stripos($result, 'stopped') !== false) {
                    return $this->successResponse('Kuyruk İşçisi durduruldu (supervisor).');
                }

                @shell_exec("pkill -f 'queue:work' 2>/dev/null");
                return $this->successResponse('Kuyruk İşçisi durduruldu.');
            } else {
                @shell_exec("wmic process where \"CommandLine like '%queue:work%' and not CommandLine like '%wmic%'\" call terminate 2>NUL");
                return $this->successResponse('Kuyruk İşçisi durduruldu.');
            }
        }

        return $this->errorResponse('Bilinmeyen işlem.');
    }

    private function getSystemStatusData(): array
    {
        $schedulerRunning = static::isSchedulerRunning();
        $schedulerLastRun = static::getSchedulerLastRun();
        $queueWorkerRunning = static::isQueueWorkerRunning();

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
