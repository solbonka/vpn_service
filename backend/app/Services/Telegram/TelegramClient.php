<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class TelegramClient
{
    private string $botToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";

        if (empty($this->botToken)) {
            throw new Exception('Telegram bot token is not configured');
        }
    }

    /**
     * Получить информацию о чате (пользователе)
     */
    public function getChat(int $chatId): ?array
    {
        try {
            $response = Http::timeout(10)->get("{$this->baseUrl}/getChat", [
                'chat_id' => $chatId
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to get chat info from Telegram API', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            $data = $response->json();

            if (!$data['ok']) {
                Log::warning('Telegram API returned error', [
                    'chat_id' => $chatId,
                    'error_code' => $data['error_code'] ?? null,
                    'description' => $data['description'] ?? null
                ]);
                return null;
            }

            return $data['result'];

        } catch (Exception $e) {
            Log::error('Exception while getting chat info', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Получить информацию о пользователе по chat_id
     */
    public function getUserInfo(int $chatId): ?array
    {
        $chatInfo = $this->getChat($chatId);

        if (!$chatInfo) {
            return null;
        }

        if ($chatInfo['type'] === 'private') {
            return [
                'id' => $chatInfo['id'],
                'first_name' => $chatInfo['first_name'] ?? null,
                'last_name' => $chatInfo['last_name'] ?? null,
                'username' => $chatInfo['username'] ?? null,
                'language_code' => $chatInfo['language_code'] ?? null,
                'is_bot' => $chatInfo['is_bot'] ?? false,
            ];
        }

        return null;
    }

    /**
     * Получить отображаемое имя пользователя
     */
    public function getUserDisplayName(int $chatId): string
    {
        $userInfo = $this->getUserInfo($chatId);

        if (!$userInfo) {
            return 'Неизвестный пользователь';
        }

        $parts = [];

        if (!empty($userInfo['first_name'])) {
            $parts[] = $userInfo['first_name'];
        }

        if (!empty($userInfo['last_name'])) {
            $parts[] = $userInfo['last_name'];
        }

        $displayName = implode(' ', $parts);

        if (!empty($userInfo['username'])) {
            $displayName .= " (@{$userInfo['username']})";
        }

        return $displayName ?: 'Пользователь';
    }

    /**
     * Проверить доступность API
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/getMe");
            return $response->successful() && $response->json('ok', false);
        } catch (Exception $e) {
            Log::error('Telegram API connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
