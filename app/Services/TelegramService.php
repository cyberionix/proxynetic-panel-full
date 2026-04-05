<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;
    private string $chatId;
    private bool $enabled;

    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token', '');
        $this->chatId  = config('services.telegram.chat_id', '');
        $this->enabled = (bool) config('services.telegram.enabled', false);
    }

    public function isConfigured(): bool
    {
        return $this->token !== '' && $this->chatId !== '';
    }

    public function sendMessage(string $text, ?string $chatId = null): bool
    {
        if (!$this->enabled || !$this->isConfigured()) {
            return false;
        }

        $targetChat = $chatId ?: $this->chatId;

        try {
            $response = Http::timeout(10)->post(
                "https://api.telegram.org/bot{$this->token}/sendMessage",
                [
                    'chat_id'    => $targetChat,
                    'text'       => $text,
                    'parse_mode' => 'HTML',
                ]
            );

            if (!$response->successful()) {
                Log::error('TELEGRAM_SEND_FAIL', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('TELEGRAM_SEND_ERROR', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendSupportNotification($ticket, ?string $messageText = null): bool
    {
        $userName = $ticket->user ? $ticket->user->full_name : 'Bilinmiyor';
        $priority = $ticket->priority ?? '-';
        $message  = strip_tags($messageText ?? '');
        $adminUrl = url('/netAdmin/supports/' . $ticket->id);

        $productLine = '';
        if ($ticket->order_id) {
            $order = $ticket->relationLoaded('order') ? $ticket->order : $ticket->order()->first();
            if ($order) {
                $productName = $order->product_data['name'] ?? '';
                if ($productName) {
                    $productLine = "📦 Ürün: <b>{$productName}</b>\n";
                }
            }
        }

        $text = "🎫 <b>Yeni Destek Talebi</b>\n\n"
            . "📋 Ticket No: <b>#{$ticket->id}</b>\n"
            . "👤 Kullanıcı: <b>{$userName}</b>\n"
            . $productLine
            . "📌 Konu: <b>{$ticket->subject}</b>\n"
            . "⚡ Öncelik: <b>{$priority}</b>\n"
            . "💬 Mesaj: {$message}\n\n"
            . "🔗 <a href=\"{$adminUrl}\">Panelde Görüntüle</a>";

        return $this->sendMessage($text);
    }

    public function sendCustomerReplyNotification($ticket, string $messageText): bool
    {
        $userName = $ticket->user ? $ticket->user->full_name : 'Bilinmiyor';
        $message  = strip_tags($messageText);
        $adminUrl = url('/netAdmin/supports/' . $ticket->id);

        if (mb_strlen($message) > 300) {
            $message = mb_substr($message, 0, 300) . '…';
        }

        $text = "💬 <b>Müşteri Yanıtı</b>\n\n"
            . "📋 Ticket No: <b>#{$ticket->id}</b>\n"
            . "👤 Kullanıcı: <b>{$userName}</b>\n"
            . "📌 Konu: <b>{$ticket->subject}</b>\n"
            . "💬 Mesaj: {$message}\n\n"
            . "🔗 <a href=\"{$adminUrl}\">Panelde Görüntüle</a>";

        return $this->sendMessage($text);
    }

    public function getUpdates(): array
    {
        if ($this->token === '') {
            return ['success' => false, 'error' => 'TELEGRAM_BOT_TOKEN ayarlanmamış.'];
        }

        try {
            $response = Http::timeout(10)->get(
                "https://api.telegram.org/bot{$this->token}/getUpdates"
            );

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'API hatası: ' . $response->status()];
            }

            $data = $response->json();
            if (!($data['ok'] ?? false) || empty($data['result'])) {
                return [
                    'success' => false,
                    'error'   => 'Sonuç bulunamadı. Lütfen önce Telegram\'da botu açıp /start gönderin.',
                ];
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
                return [
                    'success' => false,
                    'error'   => 'Private mesaj bulunamadı. Botu açıp /start gönderin.',
                ];
            }

            return ['success' => true, 'chat_id' => $chatId];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
