<?php

namespace App\Traits\Error;

use App\Services\Telegram\ErrorNotificationService;
use Throwable;

trait ErrorMonitoringTrait
{
    protected ErrorNotificationService $errorNotificationService;

    /**
     * Инициализировать сервис мониторинга ошибок
     */
    protected function initializeErrorMonitoring(): void
    {
        if (!isset($this->errorNotificationService)) {
            $this->errorNotificationService = app(ErrorNotificationService::class);
        }
    }

    /**
     * Отправить уведомление об ошибке
     */
    protected function reportError(Throwable $exception, array $context = [], string $service = null, string $method = null): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge($context, [
            'service' => $service ?: static::class,
            'method' => $method ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ]);

        $this->errorNotificationService->sendErrorNotification($exception, $context);
    }

    /**
     * Отправить уведомление о критической ошибке
     */
    protected function reportCriticalError(Throwable $exception, array $context = [], string $service = null, string $method = null): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge($context, [
            'service' => $service ?: static::class,
            'method' => $method ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ]);

        $this->errorNotificationService->sendCriticalError($exception, $context);
    }

    /**
     * Отправить предупреждение
     */
    protected function reportWarning(string $message, array $context = [], string $service = null, string $method = null): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge($context, [
            'service' => $service ?: static::class,
            'method' => $method ?: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? 'unknown',
        ]);

        $this->errorNotificationService->sendWarning($message, $context);
    }

    /**
     * Обработать исключение с автоматическим мониторингом
     */
    protected function handleException(Throwable $exception, array $context = [], bool $isCritical = false): void
    {
        if ($isCritical) {
            $this->reportCriticalError($exception, $context);
        } else {
            $this->reportError($exception, $context);
        }
    }

    /**
     * Отправить уведомление об ошибке с контекстом подписки
     */
    protected function reportSubscriptionError(Throwable $exception, $subscription, string $method, array $additionalContext = []): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge([
            'service' => static::class,
            'method' => $method,
            'subscription_id' => $subscription->id ?? null,
            'subscription_token' => $subscription->token ?? null,
            'telegraph_chat_id' => $subscription->telegraph_chat_id ?? null,
            'subscription_status' => $subscription->status->value ?? 'unknown',
            'subscription_created_at' => $subscription->created_at?->toISOString(),
        ], $additionalContext);

        $this->errorNotificationService->sendErrorNotification($exception, $context);
    }

    /**
     * Отправить критическое уведомление об ошибке с контекстом подписки
     */
    protected function reportCriticalSubscriptionError(Throwable $exception, $subscription, string $method, array $additionalContext = []): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge([
            'service' => static::class,
            'method' => $method,
            'subscription_id' => $subscription->id ?? null,
            'subscription_token' => $subscription->token ?? null,
            'telegraph_chat_id' => $subscription->telegraph_chat_id ?? null,
            'subscription_status' => $subscription->status->value ?? 'unknown',
            'subscription_created_at' => $subscription->created_at?->toISOString(),
        ], $additionalContext);

        $this->errorNotificationService->sendCriticalError($exception, $context);
    }

    /**
     * Отправить уведомление об ошибке с контекстом клиентского приложения
     */
    protected function reportClientAppError(Throwable $exception, $subscription, $client, string $method, array $additionalContext = []): void
    {
        $this->initializeErrorMonitoring();

        $context = array_merge([
            'service' => static::class,
            'method' => $method,
            'subscription_id' => $subscription->id ?? null,
            'subscription_token' => $subscription->token ?? null,
            'telegraph_chat_id' => $subscription->telegraph_chat_id ?? null,
            'client_app' => $client->name ?? 'unknown',
            'client_app_id' => $client->id ?? null,
        ], $additionalContext);

        $this->errorNotificationService->sendErrorNotification($exception, $context);
    }
}
