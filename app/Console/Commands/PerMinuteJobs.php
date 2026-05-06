<?php

namespace App\Console\Commands;

use App\Services\SupportAutoReplyService;
use Illuminate\Console\Command;

class PerMinuteJobs extends Command
{
    protected $signature = 'app:per-minute-jobs';
    protected $description = 'Dakikalık arka plan görevlerini çalıştırır';

    public function handle()
    {
        file_put_contents(storage_path('framework/scheduler-heartbeat'), time());

        $stopSignal = storage_path('framework/queue-stop-signal');
        if (file_exists($stopSignal)) {
            @unlink($stopSignal);
            @exec("pkill -f 'queue:work' 2>/dev/null");
            $pidFile = storage_path('framework/queue-worker.pid');
            if (file_exists($pidFile)) {
                $pid = (int) trim(file_get_contents($pidFile));
                if ($pid > 0) @exec("kill -9 {$pid} 2>/dev/null");
                @unlink($pidFile);
            }
        }

        $sent = SupportAutoReplyService::processPendingAutoReplies();
        if ($sent > 0) {
            $this->info("Otomatik yanıt gönderildi: {$sent}");
        }
    }
}
