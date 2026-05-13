<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifyHelper
{
    /**
     * Отправить уведомление в Telegram канал
     */
    public static function send(string $botToken, string $channelId, string $message): bool
    {
        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

            $response = Http::timeout(10)->post($url, [
                'chat_id' => $channelId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            $responseBody = $response->body();
            $responseData = $response->json();
            $statusCode = $response->status();

            Log::info('Telegram API response', [
                'status_code' => $statusCode,
                'successful' => $response->successful(),
                'response_body' => $responseBody,
                'response_data' => $responseData,
                'channel_id' => $channelId,
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to send Telegram notification', [
                    'channel_id' => $channelId,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();

            if (!$data || !($data['ok'] ?? false)) {
                Log::warning('Telegram API returned error', [
                    'channel_id' => $channelId,
                    'error_code' => $data['error_code'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);
                return false;
            }

            Log::info('Telegram notification sent successfully', [
                'channel_id' => $channelId,
                'message_id' => $responseData['result']['message_id'] ?? null,
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Exception while sending Telegram notification', [
                'channel_id' => $channelId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}

