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
        $sent = SupportAutoReplyService::processPendingAutoReplies();
        if ($sent > 0) {
            $this->info("Otomatik yanıt gönderildi: {$sent}");
        }
    }
}
