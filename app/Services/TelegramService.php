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
        if (empty($this->botToken)) {
            Log::info("Telegram Message (Mock): chatId={$chatId}, msg={$message}");
            return [
                'ok' => true,
                'result' => [
                    'message_id' => 9999,
                    'text' => $message,
                ],
            ];
        }

        $response = Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);

        return $response->json();
    }
}
