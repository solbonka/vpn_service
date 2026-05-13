<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class ErrorNotificationService
{
    private string $botToken;
    private string $baseUrl;
    private string $errorChannelId;
    private bool $notificationsEnabled;
    private string $environmentName;

    public function __construct()
    {
        $this->botToken = config('telegram.error_bot_token') ?: config('telegram.bot_token');
        $this->baseUrl = "https://api.telegram.org/bot{$this->botToken}";
        $this->errorChannelId = config('telegram.error_channel_id');
        $this->notificationsEnabled = config('telegram.error_notifications_enabled');
        $this->environmentName = config('telegram.error_environment_name') ?: $this->getDefaultEnvironmentName();

        if (empty($this->botToken)) {
            throw new Exception('Telegram error bot token is not configured. Please set TELEGRAM_ERROR_BOT_TOKEN or TELEGRAM_BOT_TOKEN in your .env file');
        }

        if (empty($this->errorChannelId)) {
            throw new Exception('Telegram error channel ID is not configured. Please set TELEGRAM_ERROR_CHANNEL_ID in your .env file');
        }
    }

    /**
     * Отправить уведомление об ошибке в канал мониторинга
     */
    public function sendErrorNotification(Throwable $exception, array $context = []): bool
    {
        if (!$this->notificationsEnabled) {
            return false;
        }

        try {
            $message = $this->formatErrorMessage($exception, $context);

            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $this->errorChannelId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if (!$response->successful()) {
                Log::error('Failed to send error notification to Telegram', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'original_error' => $exception->getMessage()
                ]);
                return false;
            }

            $data = $response->json();

            if (!$data['ok']) {
                Log::error('Telegram API returned error for error notification', [
                    'error_code' => $data['error_code'] ?? null,
                    'description' => $data['description'] ?? null,
                    'original_error' => $exception->getMessage()
                ]);
                return false;
            }

            return true;

        } catch (Exception $e) {
            Log::error('Exception while sending error notification', [
                'error' => $e->getMessage(),
                'original_error' => $exception->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить критическую ошибку с высоким приоритетом
     */
    public function sendCriticalError(Throwable $exception, array $context = []): bool
    {
        if (!$this->notificationsEnabled) {
            return false;
        }

        try {
            $message = $this->formatCriticalErrorMessage($exception, $context);

            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $this->errorChannelId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            return $this->handleResponse($response, $exception);

        } catch (Exception $e) {
            Log::error('Exception while sending critical error notification', [
                'error' => $e->getMessage(),
                'original_error' => $exception->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Отправить предупреждение
     */
    public function sendWarning(string $message, array $context = []): bool
    {
        if (!$this->notificationsEnabled) {
            return false;
        }

        try {
            $formattedMessage = $this->formatWarningMessage($message, $context);

            $response = Http::timeout(10)->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $this->errorChannelId,
                'text' => $formattedMessage,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            return $this->handleResponse($response, null, $message);

        } catch (Exception $e) {
            Log::error('Exception while sending warning notification', [
                'error' => $e->getMessage(),
                'warning_message' => $message
            ]);
            return false;
        }
    }

    /**
     * Форматировать сообщение об ошибке
     */
    private function formatErrorMessage(Throwable $exception, array $context = []): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $environment = app()->environment();
        $serverName = gethostname() ?: 'unknown';
        $prodName = $this->environmentName;

        $message = "🚨 <b>Ошибка в VPN сервисе</b>\n\n";
        $message .= "⏰ <b>Время:</b> {$timestamp}\n";
        $message .= "🌍 <b>Окружение:</b> {$environment}\n";
        $message .= "🏷️ <b>Продакшен:</b> {$prodName}\n";
        $message .= "🖥️ <b>Сервер:</b> {$serverName}\n\n";

        $message .= "❌ <b>Ошибка:</b> " . $this->escapeHtml($exception->getMessage()) . "\n";
        $message .= "📁 <b>Файл:</b> " . $this->escapeHtml($exception->getFile()) . "\n";
        $message .= "📍 <b>Строка:</b> {$exception->getLine()}\n\n";

        if (!empty($context)) {
            $message .= "📋 <b>Контекст:</b>\n";
            foreach ($context as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
                $message .= "• <b>{$key}:</b> " . $this->escapeHtml($formattedValue) . "\n";
            }
            $message .= "\n";
        }

        $message .= "🔗 <b>Трейс:</b>\n<code>" . $this->escapeHtml($this->getShortTrace($exception)) . "</code>";

        return $message;
    }

    /**
     * Форматировать сообщение о критической ошибке
     */
    private function formatCriticalErrorMessage(Throwable $exception, array $context = []): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $environment = app()->environment();
        $serverName = gethostname() ?: 'unknown';
        $prodName = $this->environmentName;

        $message = "🔥 <b>КРИТИЧЕСКАЯ ОШИБКА!</b> 🔥\n\n";
        $message .= "⏰ <b>Время:</b> {$timestamp}\n";
        $message .= "🌍 <b>Окружение:</b> {$environment}\n";
        $message .= "🏷️ <b>Продакшен:</b> {$prodName}\n";
        $message .= "🖥️ <b>Сервер:</b> {$serverName}\n\n";

        $message .= "💥 <b>Ошибка:</b> " . $this->escapeHtml($exception->getMessage()) . "\n";
        $message .= "📁 <b>Файл:</b> " . $this->escapeHtml($exception->getFile()) . "\n";
        $message .= "📍 <b>Строка:</b> {$exception->getLine()}\n\n";

        if (!empty($context)) {
            $message .= "📋 <b>Контекст:</b>\n";
            foreach ($context as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
                $message .= "• <b>{$key}:</b> " . $this->escapeHtml($formattedValue) . "\n";
            }
            $message .= "\n";
        }

        $message .= "🔗 <b>Полный трейс:</b>\n<code>" . $this->escapeHtml($exception->getTraceAsString()) . "</code>";

        return $message;
    }

    /**
     * Форматировать сообщение предупреждения
     */
    private function formatWarningMessage(string $message, array $context = []): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $environment = app()->environment();
        $serverName = gethostname() ?: 'unknown';
        $prodName = $this->environmentName;

        $formattedMessage = "⚠️ <b>Предупреждение в VPN сервисе</b>\n\n";
        $formattedMessage .= "⏰ <b>Время:</b> {$timestamp}\n";
        $formattedMessage .= "🌍 <b>Окружение:</b> {$environment}\n";
        $formattedMessage .= "🏷️ <b>Продакшен:</b> {$prodName}\n";
        $formattedMessage .= "🖥️ <b>Сервер:</b> {$serverName}\n\n";

        $formattedMessage .= "⚠️ <b>Сообщение:</b> " . $this->escapeHtml($message) . "\n\n";

        if (!empty($context)) {
            $formattedMessage .= "📋 <b>Контекст:</b>\n";
            foreach ($context as $key => $value) {
                $formattedValue = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string)$value;
                $formattedMessage .= "• <b>{$key}:</b> " . $this->escapeHtml($formattedValue) . "\n";
            }
        }

        return $formattedMessage;
    }

    /**
     * Обработать ответ от Telegram API
     */
    private function handleResponse($response, ?Throwable $exception = null, ?string $customMessage = null): bool
    {
        if (!$response->successful()) {
            Log::error('Failed to send notification to Telegram', [
                'status' => $response->status(),
                'response' => $response->body(),
                'original_error' => $exception?->getMessage(),
                'custom_message' => $customMessage
            ]);
            return false;
        }

        $data = $response->json();

        if (!$data['ok']) {
            Log::error('Telegram API returned error', [
                'error_code' => $data['error_code'] ?? null,
                'description' => $data['description'] ?? null,
                'original_error' => $exception?->getMessage(),
                'custom_message' => $customMessage
            ]);
            return false;
        }

        return true;
    }

    /**
     * Получить короткий трейс ошибки
     */
    private function getShortTrace(Throwable $exception): string
    {
        $trace = $exception->getTrace();
        $shortTrace = [];

        $maxTraceItems = min(5, count($trace));

        for ($i = 0; $i < $maxTraceItems; $i++) {
            $item = $trace[$i];
            $file = $item['file'] ?? 'unknown';
            $line = $item['line'] ?? 0;
            $function = $item['function'] ?? 'unknown';
            $class = $item['class'] ?? '';

            $shortTrace[] = "#{$i} " . ($class ? "{$class}::{$function}" : $function) . "() at {$file}:{$line}";
        }

        return implode("\n", $shortTrace);
    }

    /**
     * Получить название продакшена по умолчанию
     */
    private function getDefaultEnvironmentName(): string
    {
        $environment = app()->environment();
        $serverName = gethostname() ?: 'unknown';

        if ($environment === 'production') {
            return $serverName;
        }

        return "{$environment}-{$serverName}";
    }

    /**
     * Экранировать HTML символы
     */
    private function escapeHtml(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Проверить доступность канала ошибок
     */
    public function testErrorChannel(): bool
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/sendMessage", [
                'chat_id' => $this->errorChannelId,
                'text' => '🧪 Тестовое сообщение от системы мониторинга ошибок',
                'parse_mode' => 'HTML',
            ]);

            return $response->successful() && $response->json('ok', false);
        } catch (Exception $e) {
            Log::error('Error channel test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
