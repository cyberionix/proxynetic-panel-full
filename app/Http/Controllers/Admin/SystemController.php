<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\NotificationTemplate;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\SupportAutoReply;
use App\Models\User;
use App\Models\UserGroup;
use App\Services\NotificationTemplateService;
use App\Traits\AjaxResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        $pidFile = storage_path('framework/queue-worker.pid');
        if (file_exists($pidFile)) {
            $pid = (int) trim(file_get_contents($pidFile));
            if ($pid > 0 && file_exists("/proc/{$pid}")) {
                return true;
            }
            @unlink($pidFile);
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
        if ($test_product_config && !empty($test_product_config['product_id'])) {
            $test_product = Product::find($test_product_config['product_id']);
            $test_product_price = Price::find($test_product_config['price_id'] ?? null);
        }

        $localtonetHttpVerify = (bool) config('services.localtonet.http_verify');

        $systemStatus = $this->getSystemStatusData();

        $smsMailConfig = $this->getSmsMailConfig();

        $notificationTemplates = NotificationTemplate::orderBy('sort_order')->get()->groupBy('category');

        $campaigns = Campaign::orderByDesc('id')->get();
        $userGroups = UserGroup::all();
        $productCategories = ProductCategory::orderBy('name')->get();
        $products = Product::orderBy('name')->get(['id', 'name', 'category_id']);

        $autoInvoiceSettings = $this->loadAutoInvoiceSettings();
        $renewInvoices = Invoice::whereHas('items', function ($q) {
            $q->where('type', 'RENEW');
        })->with(['items' => function ($q) {
            $q->where('type', 'RENEW');
        }, 'user'])->orderByDesc('created_at')->limit(100)->get();

        return view('admin.pages.system.settings', compact('urls', 'test_product', 'test_product_price', 'localtonetHttpVerify', 'systemStatus', 'smsMailConfig', 'notificationTemplates', 'campaigns', 'userGroups', 'productCategories', 'products', 'autoInvoiceSettings', 'renewInvoices'));
    }

    public function systemStatusAjax()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getSystemStatusData(),
            'timestamp' => now()->format('d.m.Y H:i:s'),
        ]);
    }

    private static array $jobDescriptions = [
        'InvoiceCheckoutConfirmedNotification' => 'Fatura ödeme onayı bildirimi gönderiliyor',
        'RenewOrderNotification' => 'Sipariş yenileme faturası bildirimi gönderiliyor',
        'ResetPasswordNotification' => 'Şifre sıfırlama e-postası gönderiliyor',
        'PhoneOTPNotification' => 'Telefon doğrulama kodu gönderiliyor',
        'StopServiceOnUnpaidRenewInvoice' => 'Ödenmemiş fatura nedeniyle hizmet durdurma bildirimi',
        'SupportAnsweredNotification' => 'Destek talebi yanıtlandı bildirimi gönderiliyor',
        'SupportCreatedNotification' => 'Destek talebi oluşturuldu bildirimi gönderiliyor',
        'UpcomingInvoicePaymentNotification' => 'Yaklaşan fatura ödeme hatırlatması gönderiliyor',
        'ExampleNotify' => 'Test bildirimi',
        'ProcessInvoiceItemsWhenCheckoutJob' => 'Ödeme sonrası fatura kalemleri işleniyor (sipariş oluşturma)',
        'DeliverLocaltonetQueuedOrderJob' => 'Localtonet siparişi teslim ediliyor (proxy atama)',
        'SendQueuedNotifications' => 'Kuyrukta bekleyen bildirimler gönderiliyor',
    ];

    public function pendingJobsAjax()
    {
        $jobs = [];
        try {
            if (\Schema::hasTable('jobs')) {
                $rows = DB::table('jobs')->orderByDesc('id')->limit(50)->get();
                foreach ($rows as $row) {
                    $payload = json_decode($row->payload, true);
                    $displayName = $payload['displayName'] ?? 'Bilinmeyen';
                    $shortName = class_basename($displayName);
                    $description = self::$jobDescriptions[$shortName] ?? 'Kuyruk işi çalıştırılıyor';

                    $detail = '';
                    $commandData = null;
                    if (isset($payload['data']['command'])) {
                        try {
                            $commandData = unserialize($payload['data']['command']);
                        } catch (\Throwable $e) {}
                    }

                    if ($commandData) {
                        if (method_exists($commandData, 'toArray')) {
                            $arr = $commandData->toArray();
                            if (isset($arr['invoice_id'])) $detail .= 'Fatura #' . $arr['invoice_id'];
                            if (isset($arr['order_id'])) $detail .= ($detail ? ' | ' : '') . 'Sipariş #' . $arr['order_id'];
                        }
                        if (empty($detail) && isset($commandData->notifiables)) {
                            try {
                                $notifiables = $commandData->notifiables;
                                if (is_iterable($notifiables)) {
                                    foreach ($notifiables as $n) {
                                        $name = trim(($n->name ?? '') . ' ' . ($n->surname ?? ''));
                                        if ($name) { $detail = 'Kullanıcı: ' . $name . ' (#' . ($n->id ?? '') . ')'; break; }
                                        if ($n->email ?? null) { $detail = $n->email; break; }
                                    }
                                }
                            } catch (\Throwable $e) {}
                        }
                    }

                    $jobs[] = [
                        'id' => $row->id,
                        'queue' => $row->queue,
                        'job_name' => $shortName,
                        'full_name' => $displayName,
                        'description' => $description,
                        'detail' => $detail,
                        'attempts' => $row->attempts,
                        'created_at' => $row->created_at ? date('d.m.Y H:i:s', $row->created_at) : '-',
                        'available_at' => $row->available_at ? date('d.m.Y H:i:s', $row->available_at) : '-',
                        'reserved_at' => $row->reserved_at ? date('d.m.Y H:i:s', $row->reserved_at) : null,
                    ];
                }
            }
        } catch (\Throwable $e) {}

        return response()->json(['success' => true, 'jobs' => $jobs]);
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

            $pidFile = storage_path('framework/queue-worker.pid');

            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                $php = '/opt/plesk/php/8.3/bin/php';
                $artisan = base_path('artisan');
                $logFile = storage_path('logs/worker.log');
                $baseDir = base_path();

                $shellScript = storage_path('framework/start-queue-worker.sh');
                $scriptContent = "#!/bin/bash\ncd {$baseDir}\nnohup {$php} {$artisan} queue:work --sleep=3 --tries=3 --timeout=90 >> {$logFile} 2>&1 &\necho \$! > {$pidFile}\n";
                file_put_contents($shellScript, $scriptContent);
                chmod($shellScript, 0755);

                @exec("bash {$shellScript} 2>/dev/null");

                usleep(500000);
                if (file_exists($pidFile)) {
                    $pid = trim(file_get_contents($pidFile));
                    return $this->successResponse("Kuyruk İşçisi başlatıldı (PID: {$pid}).");
                }
                return $this->successResponse('Kuyruk İşçisi başlatıldı.');
            } else {
                $php = PHP_BINARY ?: 'php';
                $artisan = base_path('artisan');
                $logFile = storage_path('logs/worker.log');
                $baseDir = base_path();
                $batFile = storage_path('framework/queue-worker.bat');
                $cmd = "\"{$php}\" \"{$artisan}\" queue:work --sleep=3 --tries=3 --timeout=90";
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
            $pidFile = storage_path('framework/queue-worker.pid');

            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                Artisan::call('queue:restart');

                if (file_exists($pidFile)) {
                    @unlink($pidFile);
                }

                file_put_contents(storage_path('framework/queue-stop-signal'), time());

                return $this->successResponse('Kuyruk İşçisi durdurma sinyali gönderildi. Mevcut işi tamamladıktan sonra duracak.');
            } else {
                @shell_exec("wmic process where \"CommandLine like '%queue:work%' and not CommandLine like '%wmic%'\" call terminate 2>NUL");
                if (file_exists($pidFile)) @unlink($pidFile);
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

        if ($request->has('test_product')) {
            $test_product = $request->test_product;
            if (!isset($test_product['status'])) {
                $test_product['status'] = 0;
            }
            file_put_contents(config_path('test_product.php'), '<?php return ' . var_export($test_product, true) . ';');
        }

        if ($request->has('localtonet_http_verify')) {
            $localtonetHttpVerify = $request->boolean('localtonet_http_verify');
            file_put_contents(
                config_path('localtonet_settings.php'),
                '<?php return ' . var_export(['http_verify' => $localtonetHttpVerify], true) . ';'
            );
        }

        if ($request->has('auto_invoice')) {
            $ai = $request->input('auto_invoice');
            $autoInvoiceData = [
                'auto_renew_enabled' => (bool) ($ai['auto_renew_enabled'] ?? false),
                'renew_days_before_monthly' => max(1, (int) ($ai['renew_days_before_monthly'] ?? 7)),
                'renew_days_before_weekly' => max(1, (int) ($ai['renew_days_before_weekly'] ?? 2)),
                'renew_days_before_daily' => max(0, (int) ($ai['renew_days_before_daily'] ?? 1)),
                'reminder_days_before' => max(1, (int) ($ai['reminder_days_before'] ?? 3)),
                'stop_service_on_unpaid' => (bool) ($ai['stop_service_on_unpaid'] ?? false),
                'invoice_consolidation_enabled' => (bool) ($ai['invoice_consolidation_enabled'] ?? false),
                'consolidation_window_hours' => max(1, (int) ($ai['consolidation_window_hours'] ?? 1)),
            ];

            file_put_contents(
                config_path('auto_invoice_settings.php'),
                '<?php return ' . var_export($autoInvoiceData, true) . ';'
            );
        }

        if ($request->has('sms_mail')) {
            $smsMail = $request->input('sms_mail');
            $smsMailData = [
                'sms_enabled' => (bool) ($smsMail['sms_enabled'] ?? false),
                'sms_provider' => $smsMail['sms_provider'] ?? 'iletimerkezi',
                'iletimerkezi_key' => $smsMail['iletimerkezi_key'] ?? '',
                'iletimerkezi_secret' => $smsMail['iletimerkezi_secret'] ?? '',
                'iletimerkezi_origin' => $smsMail['iletimerkezi_origin'] ?? '',
                'iletimerkezi_debug' => (bool) ($smsMail['iletimerkezi_debug'] ?? false),
                'iletimerkezi_sandbox' => (bool) ($smsMail['iletimerkezi_sandbox'] ?? false),
                'mutlucell_username' => $smsMail['mutlucell_username'] ?? '',
                'mutlucell_password' => $smsMail['mutlucell_password'] ?? '',
                'mutlucell_sender' => $smsMail['mutlucell_sender'] ?? '',
                'mail_provider' => $smsMail['mail_provider'] ?? 'smtp',
                'mail_from_address' => $smsMail['mail_from_address'] ?? '',
                'mail_from_name' => $smsMail['mail_from_name'] ?? '',
                'smtp_host' => $smsMail['smtp_host'] ?? '',
                'smtp_port' => (int) ($smsMail['smtp_port'] ?? 587),
                'smtp_encryption' => $smsMail['smtp_encryption'] ?? 'tls',
                'smtp_username' => $smsMail['smtp_username'] ?? '',
                'smtp_password' => $smsMail['smtp_password'] ?? '',
                'mailjet_apikey' => $smsMail['mailjet_apikey'] ?? '',
                'mailjet_apisecret' => $smsMail['mailjet_apisecret'] ?? '',
            ];

            file_put_contents(
                config_path('sms_mail_settings.php'),
                '<?php return ' . var_export($smsMailData, true) . ';'
            );
        }

        Artisan::call('config:clear');
        Artisan::call('config:cache');

        return redirect()->route('admin.settings')->with('form_success', 'Değişiklikler başarıyla kaydedildi.');
    }

    private function sanitizeTime(string $time): string
    {
        if (preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $time)) {
            return $time;
        }
        return '10:00';
    }

    private function loadAutoInvoiceSettings(): array
    {
        $path = config_path('auto_invoice_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [
            'auto_renew_enabled' => true,
            'renew_days_before_monthly' => 7,
            'renew_days_before_weekly' => 2,
            'renew_days_before_daily' => 1,
            'reminder_days_before' => 3,
            'stop_service_on_unpaid' => true,
            'renew_run_time' => '10:00',
            'reminder_run_time' => '10:00',
            'stop_service_run_time' => '02:00',
        ];
    }

    private function getSmsMailConfig(): array
    {
        $defaults = [
            'sms_enabled' => (bool) config('services.sms.enabled', false),
            'sms_provider' => 'iletimerkezi',
            'iletimerkezi_key' => config('services.sms.iletimerkezi.key', ''),
            'iletimerkezi_secret' => config('services.sms.iletimerkezi.secret', ''),
            'iletimerkezi_origin' => config('services.sms.iletimerkezi.origin', ''),
            'iletimerkezi_debug' => (bool) config('services.sms.iletimerkezi.debug', false),
            'iletimerkezi_sandbox' => (bool) config('services.sms.iletimerkezi.sandboxMode', false),
            'mutlucell_username' => config('mutlucell.auth.username', ''),
            'mutlucell_password' => config('mutlucell.auth.password', ''),
            'mutlucell_sender' => config('mutlucell.default_sender', ''),
            'mail_provider' => config('mail.default', 'smtp'),
            'mail_from_address' => config('mail.from.address', ''),
            'mail_from_name' => config('mail.from.name', ''),
            'smtp_host' => config('mail.mailers.smtp.host', ''),
            'smtp_port' => (int) config('mail.mailers.smtp.port', 587),
            'smtp_encryption' => config('mail.mailers.smtp.encryption', 'tls'),
            'smtp_username' => config('mail.mailers.smtp.username', ''),
            'smtp_password' => config('mail.mailers.smtp.password', ''),
            'mailjet_apikey' => config('services.mailjet.key', ''),
            'mailjet_apisecret' => config('services.mailjet.secret', ''),
        ];

        $settingsPath = config_path('sms_mail_settings.php');
        if (is_file($settingsPath)) {
            $saved = require $settingsPath;
            if (is_array($saved)) {
                $defaults = array_merge($defaults, $saved);
            }
        }

        return $defaults;
    }

    public function testSmsConnection(Request $request)
    {
        $cfg = $request->input('config', []);
        $provider = $cfg['sms_provider'] ?? 'iletimerkezi';

        try {
            if ($provider === 'iletimerkezi') {
                $key = $cfg['iletimerkezi_key'] ?? '';
                $secret = $cfg['iletimerkezi_secret'] ?? '';
                if (empty($key) || empty($secret)) {
                    return $this->errorResponse('API Key ve Secret alanları boş olamaz.');
                }
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->post('https://api.iletimerkezi.com/v1/get-balance/json', [
                        'request' => [
                            'authentication' => ['key' => $key, 'hash' => $secret],
                        ],
                    ]);
                $data = $response->json();
                if (isset($data['response']['status']['code']) && $data['response']['status']['code'] == 200) {
                    $balance = $data['response']['balance']['sms'] ?? '?';
                    return $this->successResponse('Bağlantı başarılı!', ['details' => "Kalan SMS kredisi: {$balance}"]);
                }
                $msg = $data['response']['status']['message'] ?? 'Bilinmeyen hata';
                return $this->errorResponse('Bağlantı başarısız: ' . $msg);
            }

            if ($provider === 'mutlucell') {
                $username = $cfg['mutlucell_username'] ?? '';
                $password = $cfg['mutlucell_password'] ?? '';
                if (empty($username) || empty($password)) {
                    return $this->errorResponse('Kullanıcı adı ve şifre alanları boş olamaz.');
                }
                $xml = '<?xml version="1.0" encoding="UTF-8"?><smskredi ka="' . htmlspecialchars($username) . '" pwd="' . htmlspecialchars($password) . '"/>';
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withBody($xml, 'text/xml; charset=UTF-8')
                    ->post('https://smsgw.mutlucell.com/smsgw-ws/gtcrdtex');
                $body = trim($response->body());
                if (str_starts_with($body, '$')) {
                    $balance = substr($body, 1);
                    return $this->successResponse('Bağlantı başarılı!', ['details' => "Kalan SMS kredisi: {$balance}"]);
                }
                if (is_numeric($body)) {
                    return $this->errorResponse('Mutlucell hata kodu: ' . $body);
                }
                return $this->errorResponse('Bağlantı başarısız: ' . mb_substr($body, 0, 200));
            }

            return $this->errorResponse('Geçersiz sağlayıcı.');
        } catch (\Throwable $e) {
            return $this->errorResponse('Bağlantı hatası: ' . $e->getMessage());
        }
    }

    public function testSmsSend(Request $request)
    {
        $cfg = $request->input('config', []);
        $number = $request->input('number');
        $message = $request->input('message', 'Test SMS');
        $provider = $cfg['sms_provider'] ?? 'iletimerkezi';

        if (empty($number)) {
            return $this->errorResponse('Telefon numarası gereklidir.');
        }

        try {
            if ($provider === 'iletimerkezi') {
                $key = $cfg['iletimerkezi_key'] ?? '';
                $secret = $cfg['iletimerkezi_secret'] ?? '';
                $origin = $cfg['iletimerkezi_origin'] ?? '';
                if (empty($key) || empty($secret)) {
                    return $this->errorResponse('API Key ve Secret alanları boş olamaz.');
                }
                $data = [
                    'request' => [
                        'authentication' => ['key' => $key, 'hash' => $secret],
                        'order' => [
                            'sender' => $origin,
                            'sendDateTime' => '',
                            'iys' => 0,
                            'message' => [
                                'text' => $message,
                                'receipents' => ['number' => [$number]],
                            ],
                        ],
                    ],
                ];
                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->post('https://api.iletimerkezi.com/v1/send-sms/json', $data);
                $result = $response->json();
                if (isset($result['response']['status']['code']) && $result['response']['status']['code'] == 200) {
                    $orderId = $result['response']['order']['id'] ?? '';
                    return $this->successResponse('SMS başarıyla gönderildi!', ['details' => "Sipariş No: {$orderId}"]);
                }
                $msg = $result['response']['status']['message'] ?? 'Bilinmeyen hata';
                return $this->errorResponse('SMS gönderilemedi: ' . $msg);
            }

            if ($provider === 'mutlucell') {
                $username = $cfg['mutlucell_username'] ?? '';
                $password = $cfg['mutlucell_password'] ?? '';
                $sender = $cfg['mutlucell_sender'] ?? '';
                if (empty($username) || empty($password)) {
                    return $this->errorResponse('Kullanıcı adı ve şifre alanları boş olamaz.');
                }
                $xmlEl = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><smspack/>');
                $xmlEl->addAttribute('ka', $username);
                $xmlEl->addAttribute('pwd', $password);
                $xmlEl->addAttribute('org', $sender);
                $xmlEl->addAttribute('charset', 'turkish');
                $mesaj = $xmlEl->addChild('mesaj');
                $mesaj->addChild('metin', $message);
                $mesaj->addChild('nums', $number);
                $xml = $xmlEl->asXML();

                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->withBody($xml, 'text/xml; charset=UTF-8')
                    ->post('https://smsgw.mutlucell.com/smsgw-ws/sndblkex');
                $body = trim($response->body());
                if (str_starts_with($body, '$')) {
                    $msgId = substr($body, 1);
                    return $this->successResponse('SMS başarıyla gönderildi!', ['details' => "Mesaj ID: {$msgId}"]);
                }
                if (is_numeric($body) && (int) $body > 1000) {
                    return $this->successResponse('SMS başarıyla gönderildi!', ['details' => "Mesaj ID: {$body}"]);
                }
                return $this->errorResponse('SMS gönderilemedi. Hata kodu: ' . mb_substr($body, 0, 200));
            }

            return $this->errorResponse('Geçersiz sağlayıcı.');
        } catch (\Throwable $e) {
            return $this->errorResponse('SMS gönderim hatası: ' . $e->getMessage());
        }
    }

    public function testMailConnection(Request $request)
    {
        $cfg = $request->input('config', []);
        $provider = $cfg['mail_provider'] ?? 'smtp';

        try {
            if ($provider === 'smtp') {
                $host = $cfg['smtp_host'] ?? '';
                $port = (int) ($cfg['smtp_port'] ?? 587);
                $encryption = $cfg['smtp_encryption'] ?? 'tls';
                $username = $cfg['smtp_username'] ?? '';
                $password = $cfg['smtp_password'] ?? '';

                if (empty($host)) {
                    return $this->errorResponse('SMTP Host alanı boş olamaz.');
                }

                $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                    $host,
                    $port,
                    $encryption === 'tls' || $encryption === 'ssl'
                );
                if (!empty($username)) {
                    $transport->setUsername($username);
                }
                if (!empty($password)) {
                    $transport->setPassword($password);
                }
                $transport->start();
                $transport->stop();

                return $this->successResponse('SMTP bağlantısı başarılı!', ['details' => "{$host}:{$port} ({$encryption})"]);
            }

            if ($provider === 'mailjet') {
                $apiKey = $cfg['mailjet_apikey'] ?? '';
                $apiSecret = $cfg['mailjet_apisecret'] ?? '';
                if (empty($apiKey) || empty($apiSecret)) {
                    return $this->errorResponse('Mailjet API Key ve Secret alanları boş olamaz.');
                }
                $response = \Illuminate\Support\Facades\Http::timeout(10)
                    ->withBasicAuth($apiKey, $apiSecret)
                    ->get('https://api.mailjet.com/v3/REST/apikey');
                if ($response->successful()) {
                    return $this->successResponse('Mailjet bağlantısı başarılı!', ['details' => 'API kimlik doğrulaması geçerli.']);
                }
                return $this->errorResponse('Mailjet bağlantısı başarısız: HTTP ' . $response->status());
            }

            return $this->errorResponse('Geçersiz sağlayıcı.');
        } catch (\Throwable $e) {
            return $this->errorResponse('Bağlantı hatası: ' . $e->getMessage());
        }
    }

    public function testMailSend(Request $request)
    {
        $cfg = $request->input('config', []);
        $toEmail = $request->input('email');
        $subject = $request->input('subject', 'Test E-postası');
        $provider = $cfg['mail_provider'] ?? 'smtp';
        $fromAddress = $cfg['mail_from_address'] ?? config('mail.from.address');
        $fromName = $cfg['mail_from_name'] ?? config('mail.from.name');

        if (empty($toEmail)) {
            return $this->errorResponse('E-posta adresi gereklidir.');
        }

        try {
            if ($provider === 'smtp') {
                $host = $cfg['smtp_host'] ?? '';
                $port = (int) ($cfg['smtp_port'] ?? 587);
                $encryption = $cfg['smtp_encryption'] ?? 'tls';
                $username = $cfg['smtp_username'] ?? '';
                $password = $cfg['smtp_password'] ?? '';

                $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
                    $host,
                    $port,
                    $encryption === 'tls' || $encryption === 'ssl'
                );
                if (!empty($username)) {
                    $transport->setUsername($username);
                }
                if (!empty($password)) {
                    $transport->setPassword($password);
                }

                $email = (new \Symfony\Component\Mime\Email())
                    ->from(new \Symfony\Component\Mime\Address($fromAddress, $fromName))
                    ->to($toEmail)
                    ->subject($subject)
                    ->html('<div style="font-family:Arial,sans-serif;padding:20px;"><h2>Test E-postası</h2><p>Bu bir test e-postasıdır. Mail ayarlarınız doğru çalışıyor.</p><p style="color:#888;font-size:12px;">Gönderim zamanı: ' . now()->format('d.m.Y H:i:s') . '</p></div>');

                $mailer = new \Symfony\Component\Mailer\Mailer($transport);
                $mailer->send($email);

                return $this->successResponse('Test e-postası başarıyla gönderildi!', ['details' => "Alıcı: {$toEmail}"]);
            }

            if ($provider === 'mailjet') {
                $apiKey = $cfg['mailjet_apikey'] ?? '';
                $apiSecret = $cfg['mailjet_apisecret'] ?? '';
                if (empty($apiKey) || empty($apiSecret)) {
                    return $this->errorResponse('Mailjet API Key ve Secret alanları boş olamaz.');
                }

                $response = \Illuminate\Support\Facades\Http::timeout(15)
                    ->withBasicAuth($apiKey, $apiSecret)
                    ->post('https://api.mailjet.com/v3.1/send', [
                        'Messages' => [[
                            'From' => ['Email' => $fromAddress, 'Name' => $fromName],
                            'To' => [['Email' => $toEmail]],
                            'Subject' => $subject,
                            'HTMLPart' => '<div style="font-family:Arial,sans-serif;padding:20px;"><h2>Test E-postası</h2><p>Bu bir test e-postasıdır. Mailjet ayarlarınız doğru çalışıyor.</p><p style="color:#888;font-size:12px;">Gönderim zamanı: ' . now()->format('d.m.Y H:i:s') . '</p></div>',
                        ]],
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $status = $data['Messages'][0]['Status'] ?? 'unknown';
                    if ($status === 'success') {
                        return $this->successResponse('Test e-postası başarıyla gönderildi!', ['details' => "Alıcı: {$toEmail}"]);
                    }
                    $errors = json_encode($data['Messages'][0]['Errors'] ?? []);
                    return $this->errorResponse('Mailjet gönderim hatası: ' . $errors);
                }
                return $this->errorResponse('Mailjet API hatası: HTTP ' . $response->status());
            }

            return $this->errorResponse('Geçersiz sağlayıcı.');
        } catch (\Throwable $e) {
            return $this->errorResponse('Mail gönderim hatası: ' . $e->getMessage());
        }
    }

    public function getNotificationTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    public function updateNotificationTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);

        $template->update([
            'sms_enabled' => $request->boolean('sms_enabled'),
            'mail_enabled' => $request->boolean('mail_enabled'),
            'admin_sms_enabled' => $request->boolean('admin_sms_enabled'),
            'admin_mail_enabled' => $request->boolean('admin_mail_enabled'),
            'sms_content' => $request->input('sms_content', ''),
            'mail_subject' => $request->input('mail_subject', ''),
            'mail_content' => $request->input('mail_content', ''),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->successResponse('Şablon başarıyla güncellendi.');
    }

    public function toggleNotificationTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'aktif' : 'pasif';
        return $this->successResponse("Şablon {$status} edildi.");
    }

    // ─── Kampanya Yönetimi ───

    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:sms,mail,both',
            'target_type' => 'required|string',
        ]);

        $filters = [];
        $targetType = $request->input('target_type');

        if ($targetType === 'user_group' && $request->filled('user_group_ids')) {
            $filters['user_group_ids'] = $request->input('user_group_ids');
        } elseif ($targetType === 'product_category' && $request->filled('category_ids')) {
            $filters['category_ids'] = $request->input('category_ids');
        } elseif ($targetType === 'product' && $request->filled('product_ids')) {
            $filters['product_ids'] = $request->input('product_ids');
        } elseif ($targetType === 'custom' && $request->filled('user_ids')) {
            $filters['user_ids'] = $request->input('user_ids');
        }

        $campaign = Campaign::create([
            'name' => $request->input('name'),
            'channel' => $request->input('channel'),
            'target_type' => $targetType,
            'target_filters' => $filters,
            'sms_content' => $request->input('sms_content', ''),
            'mail_subject' => $request->input('mail_subject', ''),
            'mail_content' => $request->input('mail_content', ''),
            'status' => 'draft',
            'created_by' => Auth::guard('admin')->id(),
        ]);

        return $this->successResponse('Kampanya başarıyla kaydedildi.', ['campaign' => $campaign]);
    }

    public function getCampaign(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        return response()->json(['success' => true, 'data' => $campaign]);
    }

    public function updateCampaign(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->status === 'sent') {
            return $this->errorResponse('Gönderilmiş kampanya düzenlenemez.');
        }

        $filters = [];
        $targetType = $request->input('target_type', $campaign->target_type);

        if ($targetType === 'user_group' && $request->filled('user_group_ids')) {
            $filters['user_group_ids'] = $request->input('user_group_ids');
        } elseif ($targetType === 'product_category' && $request->filled('category_ids')) {
            $filters['category_ids'] = $request->input('category_ids');
        } elseif ($targetType === 'product' && $request->filled('product_ids')) {
            $filters['product_ids'] = $request->input('product_ids');
        } elseif ($targetType === 'custom' && $request->filled('user_ids')) {
            $filters['user_ids'] = $request->input('user_ids');
        }

        $campaign->update([
            'name' => $request->input('name', $campaign->name),
            'channel' => $request->input('channel', $campaign->channel),
            'target_type' => $targetType,
            'target_filters' => $filters,
            'sms_content' => $request->input('sms_content', $campaign->sms_content),
            'mail_subject' => $request->input('mail_subject', $campaign->mail_subject),
            'mail_content' => $request->input('mail_content', $campaign->mail_content),
        ]);

        return $this->successResponse('Kampanya güncellendi.', ['campaign' => $campaign->fresh()]);
    }

    public function deleteCampaign(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        $campaign->delete();
        return $this->successResponse('Kampanya silindi.');
    }

    public function previewCampaignRecipients(Request $request)
    {
        $temp = new Campaign([
            'target_type' => $request->input('target_type', 'all'),
            'target_filters' => $request->input('target_filters', []),
            'channel' => $request->input('channel', 'both'),
        ]);

        $recipients = $temp->resolveRecipients();
        $list = $recipients->map(fn($u) => [
            'id' => $u->id,
            'name' => $u->first_name . ' ' . $u->last_name,
            'email' => $u->email,
            'phone' => $u->phone,
        ])->values();

        return response()->json([
            'success' => true,
            'count' => $recipients->count(),
            'recipients' => $list->take(50),
        ]);
    }

    public function sendCampaign(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        if ($campaign->status === 'sent') {
            return $this->errorResponse('Bu kampanya zaten gönderildi.');
        }
        if ($campaign->status === 'sending') {
            return $this->errorResponse('Bu kampanya şu anda gönderiliyor.');
        }

        $recipients = $campaign->resolveRecipients();
        if ($recipients->isEmpty()) {
            return $this->errorResponse('Hedef kitlede alıcı bulunamadı.');
        }

        $campaign->update([
            'status' => 'sending',
            'total_recipients' => $recipients->count(),
        ]);

        $smsMailSettings = $this->loadSmsMailSettingsStatic();
        $sentSms = 0;
        $sentMail = 0;
        $failedCount = 0;

        foreach ($recipients as $user) {
            try {
                $variables = [
                    'ad' => $user->first_name,
                    'soyad' => $user->last_name,
                    'email' => $user->email,
                    'site_url' => config('app.url', url('/')),
                    'site_adi' => config('app.name', 'Proxynetic'),
                ];

                if (in_array($campaign->channel, ['mail', 'both']) && $user->email) {
                    $subject = $this->replaceCampaignVars($campaign->mail_subject ?? '', $variables);
                    $htmlContent = $this->replaceCampaignVars($campaign->mail_content ?? '', $variables);
                    if (!empty($htmlContent)) {
                        try {
                            Mail::html($htmlContent, function ($message) use ($user, $subject) {
                                $message->to($user->email)->subject($subject);
                            });
                            $sentMail++;
                        } catch (\Throwable $e) {
                            Log::error('Kampanya mail hatası', ['campaign_id' => $campaign->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
                            $failedCount++;
                        }
                    }
                }

                if (in_array($campaign->channel, ['sms', 'both']) && $user->phone && !empty($smsMailSettings['sms_enabled'])) {
                    $smsContent = $this->replaceCampaignVars($campaign->sms_content ?? '', $variables);
                    if (!empty($smsContent)) {
                        try {
                            $this->sendCampaignSms($smsMailSettings, $user->phone, $smsContent);
                            $sentSms++;
                        } catch (\Throwable $e) {
                            Log::error('Kampanya SMS hatası', ['campaign_id' => $campaign->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
                            $failedCount++;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error('Kampanya gönderim hatası', ['campaign_id' => $campaign->id, 'user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        }

        $campaign->update([
            'status' => 'sent',
            'sent_sms' => $sentSms,
            'sent_mail' => $sentMail,
            'failed_count' => $failedCount,
            'sent_at' => now(),
        ]);

        return $this->successResponse("Kampanya gönderimi tamamlandı! SMS: {$sentSms}, E-posta: {$sentMail}, Başarısız: {$failedCount}");
    }

    public function duplicateCampaign(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);
        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Kopya)';
        $newCampaign->status = 'draft';
        $newCampaign->sent_sms = 0;
        $newCampaign->sent_mail = 0;
        $newCampaign->failed_count = 0;
        $newCampaign->total_recipients = 0;
        $newCampaign->sent_at = null;
        $newCampaign->created_by = Auth::guard('admin')->id();
        $newCampaign->save();

        return $this->successResponse('Kampanya kopyalandı.', ['campaign' => $newCampaign]);
    }

    private function replaceCampaignVars(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) $value, $template);
        }
        return $template;
    }

    private function sendCampaignSms(array $settings, string $phone, string $message): void
    {
        $provider = $settings['sms_provider'] ?? 'mutlucell';

        if ($provider === 'iletimerkezi') {
            \Illuminate\Support\Facades\Http::timeout(15)->post('https://api.iletimerkezi.com/v1/send-sms/json', [
                'request' => [
                    'authentication' => [
                        'key' => $settings['iletimerkezi_key'] ?? '',
                        'hash' => $settings['iletimerkezi_secret'] ?? '',
                    ],
                    'order' => [
                        'sender' => $settings['iletimerkezi_origin'] ?? '',
                        'sendDateTime' => '',
                        'iys' => 0,
                        'message' => [
                            'text' => $message,
                            'receipents' => ['number' => [$phone]],
                        ],
                    ],
                ],
            ]);
        } else {
            $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><smspack/>');
            $xml->addAttribute('ka', $settings['mutlucell_username'] ?? '');
            $xml->addAttribute('pwd', $settings['mutlucell_password'] ?? '');
            $xml->addAttribute('org', $settings['mutlucell_sender'] ?? '');
            $xml->addAttribute('charset', 'turkish');
            $mesaj = $xml->addChild('mesaj');
            $mesaj->addChild('metin', $message);
            $mesaj->addChild('nums', $phone);

            \Illuminate\Support\Facades\Http::timeout(15)
                ->withBody($xml->asXML(), 'text/xml; charset=UTF-8')
                ->post('https://smsgw.mutlucell.com/smsgw-ws/sndblkex');
        }
    }

    private function loadSmsMailSettingsStatic(): array
    {
        $path = config_path('sms_mail_settings.php');
        if (is_file($path)) {
            $data = require $path;
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    public function saveSiteSettings(Request $request)
    {
        try {
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            $vars = [
                'BRAND_NAME'           => $request->input('brand_name', ''),
                'BRAND_CUSTOMER_PANEL_TITLE' => $request->input('brand_clientarea_title', ''),
                'BASE_APP_URL'         => $request->input('brand_base_url', ''),
                'BRAND_LOGO_PATH'      => $request->input('brand_logo', ''),
                'BRAND_LOGO_DARK'      => $request->input('brand_logo_dark', ''),
                'BRAND_FAVICON'        => $request->input('brand_favicon', ''),
                'BRAND_INFO_PHONE_NUMBER' => $request->input('brand_phone', ''),
                'BRAND_INFO_EMAIL'     => $request->input('brand_email', ''),
                'BRAND_ADDRESS_LINE_1' => $request->input('brand_address1', ''),
                'BRAND_ADDRESS_LINE_2' => $request->input('brand_address2', ''),
                'BRAND_WEBSITE'        => $request->input('brand_website', ''),
                'BRAND_FACEBOOK'       => $request->input('brand_facebook', ''),
                'BRAND_TWITTER'        => $request->input('brand_twitter', ''),
                'BRAND_INSTAGRAM'      => $request->input('brand_instagram', ''),
                'BRAND_LINKEDIN'       => $request->input('brand_linkedin', ''),
                'BRAND_YOUTUBE'        => $request->input('brand_youtube', ''),
            ];

            foreach ($vars as $key => $value) {
                $escaped = str_contains($value, ' ') ? '"' . $value . '"' : $value;
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$escaped}";
                }
            }

            file_put_contents($envPath, $envContent);

            Artisan::call('config:clear');
            Artisan::call('config:cache');

            return response()->json(['success' => true, 'message' => 'Site ayarları başarıyla kaydedildi.']);
        } catch (\Throwable $e) {
            Log::error('SITE_SETTINGS_SAVE_FAIL', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }

    public function saveTelegramSettings(Request $request)
    {
        try {
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            $vars = [
                'TELEGRAM_BOT_TOKEN' => $request->input('telegram_bot_token', ''),
                'TELEGRAM_CHAT_ID'   => $request->input('telegram_chat_id', ''),
                'TELEGRAM_ENABLED'   => $request->has('telegram_enabled') ? 'true' : 'false',
            ];

            foreach ($vars as $key => $value) {
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }

            file_put_contents($envPath, $envContent);

            Artisan::call('config:clear');
            Artisan::call('config:cache');

            return response()->json(['success' => true, 'message' => 'Telegram ayarları kaydedildi.']);
        } catch (\Throwable $e) {
            Log::error('TELEGRAM_SETTINGS_SAVE_FAIL', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }

    public function testTelegram(Request $request)
    {
        try {
            $token = $request->input('telegram_bot_token', config('services.telegram.bot_token', ''));
            $chatId = $request->input('telegram_chat_id', config('services.telegram.chat_id', ''));

            if (empty($token) || empty($chatId)) {
                return response()->json(['success' => false, 'message' => 'Bot Token ve Chat ID gereklidir.']);
            }

            $text = "✅ <b>Test Bildirimi</b>\n\n"
                . "Telegram bildirim sistemi başarıyla çalışıyor.\n"
                . "📅 Tarih: " . now()->format('d/m/Y H:i:s');

            $response = \Illuminate\Support\Facades\Http::timeout(10)->post(
                "https://api.telegram.org/bot{$token}/sendMessage",
                [
                    'chat_id'    => $chatId,
                    'text'       => $text,
                    'parse_mode' => 'HTML',
                ]
            );

            if ($response->successful()) {
                return response()->json(['success' => true, 'message' => 'Test mesajı başarıyla gönderildi!']);
            }

            $body = $response->json();
            return response()->json(['success' => false, 'message' => 'Telegram hatası: ' . ($body['description'] ?? 'Bilinmeyen hata')]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function findTelegramChatId(Request $request)
    {
        try {
            $token = $request->input('telegram_bot_token', config('services.telegram.bot_token', ''));

            if (empty($token)) {
                return response()->json(['success' => false, 'message' => 'Bot Token gereklidir.']);
            }

            $response = \Illuminate\Support\Facades\Http::timeout(10)->get(
                "https://api.telegram.org/bot{$token}/getUpdates"
            );

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'API hatası: ' . $response->status()]);
            }

            $data = $response->json();
            if (!($data['ok'] ?? false) || empty($data['result'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sonuç bulunamadı. Lütfen önce Telegram\'da botu açıp /start gönderin, sonra tekrar deneyin.',
                ]);
            }

            $chatId = null;
            foreach (array_reverse($data['result']) as $update) {
                $msg = $update['message'] ?? null;
                if ($msg && ($msg['chat']['type'] ?? '') === 'private') {
                    $chatId = $msg['chat']['id'];
                    break;
                }
            }

            if (!$chatId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Private mesaj bulunamadı. Telegram\'da botu açıp /start gönderin.',
                ]);
            }

            return response()->json(['success' => true, 'chat_id' => (string) $chatId]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
        }
    }

    public function saveParasutSettings(Request $request)
    {
        try {
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            $vars = [
                'PARASUT_CLIENT_ID'          => $request->input('parasut_client_id', ''),
                'PARASUT_CLIENT_SECRET'      => $request->input('parasut_client_secret', ''),
                'PARASUT_COMPANY_ID'         => $request->input('parasut_company_id', ''),
                'PARASUT_USERNAME'           => $request->input('parasut_username', ''),
                'PARASUT_PASSWORD'           => $request->input('parasut_password', ''),
                'PARASUT_REDIRECT_URI'       => $request->input('parasut_redirect_uri', 'urn:ietf:wg:oauth:2.0:oob'),
                'PARASUT_IS_STAGE'           => $request->has('parasut_is_stage') ? 'true' : 'false',
                'PARASUT_ACCOUNT_ID'         => $request->input('parasut_account_id', ''),
                'PARASUT_INVOICE_SERIES'     => $request->input('parasut_invoice_series', 'AIBC'),
                'PARASUT_VAT_EXEMPTION_CODE' => $request->input('parasut_vat_exemption_code', '335'),
                'PARASUT_AUTO_FORMALIZE'     => $request->input('parasut_auto_formalize') == '1' ? 'true' : 'false',
                'PARASUT_FORMALIZE_DAYS'     => $request->input('parasut_formalize_days', '3'),
            ];

            foreach ($vars as $key => $value) {
                $escaped = str_contains($value, ' ') ? '"' . $value . '"' : $value;
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$escaped}";
                }
            }

            file_put_contents($envPath, $envContent);

            Artisan::call('config:clear');
            Artisan::call('config:cache');

            return response()->json(['success' => true, 'message' => 'Paraşüt ayarları kaydedildi.']);
        } catch (\Throwable $e) {
            Log::error('PARASUT_SETTINGS_SAVE_FAIL', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }

    public function testParasutConnection(Request $request)
    {
        try {
            $clientId = $request->input('parasut_client_id', config('parasut.connection.client_id'));
            $clientSecret = $request->input('parasut_client_secret', config('parasut.connection.client_secret'));
            $companyId = $request->input('parasut_company_id', config('parasut.connection.company_id'));
            $username = $request->input('parasut_username', config('parasut.connection.username'));
            $password = $request->input('parasut_password', config('parasut.connection.password'));
            $redirectUri = $request->input('parasut_redirect_uri', config('parasut.connection.redirect_uri', 'urn:ietf:wg:oauth:2.0:oob'));
            $isStage = $request->has('parasut_is_stage');

            if (!$clientId || !$clientSecret || !$companyId || !$username || !$password) {
                return response()->json(['success' => false, 'message' => 'Tüm API bilgileri doldurulmalıdır.']);
            }

            $client = new \yedincisenol\Parasut\Client($clientId, $clientSecret, $redirectUri, $username, $password, $companyId, $isStage);
            $client->login();

            return response()->json(['success' => true, 'message' => 'Paraşüt bağlantısı başarılı!']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Bağlantı hatası: ' . $e->getMessage()]);
        }
    }

    public function saveNestpaySettings(Request $request)
    {
        try {
            $envPath = base_path('.env');
            $envContent = file_get_contents($envPath);

            $vars = [
                'NESTPAY_CLIENT_ID'   => $request->input('nestpay_client_id', ''),
                'NESTPAY_STORE_KEY'   => $request->input('nestpay_store_key', ''),
                'NESTPAY_GATEWAY_URL' => $request->input('nestpay_gateway_url', 'https://entegrasyon.asseco-see.com.tr/fim/est3dgate'),
                'NESTPAY_ENABLED'     => $request->has('nestpay_enabled') ? 'true' : 'false',
            ];

            foreach ($vars as $key => $value) {
                $escaped = str_contains($value, ' ') ? '"' . $value . '"' : $value;
                if (preg_match("/^{$key}=.*/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$escaped}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$escaped}";
                }
            }

            file_put_contents($envPath, $envContent);

            Artisan::call('config:clear');
            Artisan::call('config:cache');

            return response()->json(['success' => true, 'message' => 'İşbank/Nestpay ayarları kaydedildi.']);
        } catch (\Throwable $e) {
            Log::error('NESTPAY_SETTINGS_SAVE_FAIL', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Hata: ' . $e->getMessage()], 500);
        }
    }
}
