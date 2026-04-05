<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class TelegramTest extends Command
{
    protected $signature = 'telegram:test';
    protected $description = 'Telegram bot bağlantısını test et';

    public function handle()
    {
        $tg = new TelegramService();

        if (!$tg->isConfigured()) {
            $this->error('TELEGRAM_BOT_TOKEN veya TELEGRAM_CHAT_ID .env dosyasında ayarlanmamış.');
            return 1;
        }

        $this->info('Telegram test mesajı gönderiliyor...');

        $text = "✅ <b>Test Bildirimi</b>\n\n"
            . "Telegram bildirim sistemi başarıyla çalışıyor.\n"
            . "📅 Tarih: " . now()->format('d/m/Y H:i:s');

        $result = $tg->sendMessage($text);

        if ($result) {
            $this->info('Mesaj başarıyla gönderildi!');
            return 0;
        }

        $this->error('Mesaj gönderilemedi. Logları kontrol edin.');
        return 1;
    }
}
