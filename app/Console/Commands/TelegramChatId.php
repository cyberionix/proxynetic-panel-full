<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramChatId extends Command
{
    protected $signature = 'telegram:chat-id';
    protected $description = 'Telegram Chat ID değerini getUpdates ile bul';

    public function handle()
    {
        $tg = new TelegramService();

        $token = config('services.telegram.bot_token', '');
        if ($token === '') {
            $this->error('TELEGRAM_BOT_TOKEN .env dosyasında ayarlanmamış.');
            return 1;
        }

        $masked = substr($token, 0, 6) . '...' . substr($token, -4);
        $this->info("Bot Token: {$masked}");
        $this->info('getUpdates çağrılıyor...');

        $result = $tg->getUpdates();

        if ($result['success']) {
            $this->newLine();
            $this->info("Chat ID bulundu: {$result['chat_id']}");
            $this->newLine();
            $this->line(".env dosyanıza ekleyin:");
            $this->line("TELEGRAM_CHAT_ID={$result['chat_id']}");
            return 0;
        }

        $this->error($result['error']);
        return 1;
    }
}
