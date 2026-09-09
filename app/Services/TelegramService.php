<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
    }

    /**
     * Send notification message to a Telegram chat.
     */
    public function sendMessage(string $chatId, string $message): array
    {
        // Honest behaviour: with no bot token we do NOT fabricate a success
        // response with a fake message_id. The caller records a FAILED event.
        if (empty($this->botToken)) {
            Log::warning("Telegram not configured (missing TELEGRAM_BOT_TOKEN): message to chat {$chatId} was NOT sent.");

            return [
                'ok' => false,
                'configured' => false,
                'description' => 'Telegram bot token is not configured (missing TELEGRAM_BOT_TOKEN); no message was sent.',
            ];
        }

        try {
            $response = Http::acceptJson()
                ->timeout(15)
                ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                    'chat_id' => $chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                ]);
        } catch (\Throwable $e) {
            Log::error("Telegram sendMessage threw: {$e->getMessage()}");

            return [
                'ok' => false,
                'configured' => true,
                'description' => $e->getMessage(),
            ];
        }

        $json = $response->json();

        return is_array($json) ? $json : ['ok' => false, 'description' => $response->body()];
    }
}
