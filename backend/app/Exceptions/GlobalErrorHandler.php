<?php

namespace App\Exceptions;

use App\Services\Telegram\ErrorNotificationService;
use Error;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use League\OAuth2\Server\Exception\OAuthServerException;
use ParseError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use TypeError;

class GlobalErrorHandler extends ExceptionHandler
{
    protected ErrorNotificationService $errorNotificationService;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->errorNotificationService = app(ErrorNotificationService::class);
    }

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->handleErrorReporting($e);
        });
    }

    public function render($request, Throwable $e): Response
    {
        $this->handleErrorReporting($e, $request);

        return parent::render($request, $e);
    }

    /**
     * Обработать отправку уведомления об ошибке
     */
    private function handleErrorReporting(Throwable $e, ?Request $request = null): void
    {
        try {
            if ($this->shouldSkipError($e)) {
                return;
            }

            $isCritical = $this->isCriticalError($e);

            $context = $this->prepareErrorContext($e, $request);

            if ($isCritical) {
                $this->errorNotificationService->sendCriticalError($e, $context);
            } else {
                $this->errorNotificationService->sendErrorNotification($e, $context);
            }

        } catch (Throwable $notificationException) {
            Log::error('Failed to send error notification to Telegram', [
                'original_error' => $e->getMessage(),
                'notification_error' => $notificationException->getMessage(),
            ]);
        }
    }

    /**
     * Определить, нужно ли пропустить эту ошибку
     */
    private function shouldSkipError(Throwable $e): bool
    {

        $skipErrors = [
            ValidationException::class,
            AuthenticationException::class,
            AuthorizationException::class,
            NotFoundHttpException::class,
            MethodNotAllowedHttpException::class,
            OAuthServerException::class,
        ];

        foreach ($skipErrors as $skipError) {
            if ($e instanceof $skipError) {
                return true;
            }
        }

        if (app()->environment('production') && method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
            if ($statusCode === 404) {
                return true;
            }
        }

        return false;
    }

    /**
     * Определить, является ли ошибка критической
     */
    private function isCriticalError(Throwable $e): bool
    {
        $criticalErrors = [
            QueryException::class,
            Error::class,
            ParseError::class,
            TypeError::class,
        ];

        foreach ($criticalErrors as $criticalError) {
            if ($e instanceof $criticalError) {
                return true;
            }
        }

        if (method_exists($e, 'getStatusCode')) {
            $statusCode = $e->getStatusCode();
            return $statusCode >= 500;
        }

        $criticalPatterns = [
            'database connection',
            'redis connection',
            'memory exhausted',
            'fatal error',
            'maximum execution time',
            'out of memory',
            'connection refused',
            'timeout',
        ];

        $message = strtolower($e->getMessage());
        foreach ($criticalPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Подготовить контекст для отправки ошибки
     */
    private function prepareErrorContext(Throwable $e, ?Request $request = null): array
    {
        $context = [
            'exception_class' => get_class($e),
            'environment' => app()->environment(),
            'server_name' => gethostname() ?: 'unknown',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
        ];

        if ($request) {
            $context['request'] = [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'headers' => $this->sanitizeHeaders($request->headers->all()),
            ];

            if ($request->user()) {
                $context['user'] = [
                    'id' => $request->user()->id,
                    'email' => $request->user()->email ?? null,
                    'telegram_id' => $request->user()->telegram_id ?? null,
                ];
            }
        }

        if (session()->isStarted()) {
            $context['session'] = [
                'id' => session()->getId(),
                'data' => $this->sanitizeSessionData(session()->all()),
            ];
        }

        $context['memory'] = [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'limit' => ini_get('memory_limit'),
        ];

        return $context;
    }

    /**
     * Очистить заголовки от чувствительной информации
     */
    private function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'cookie',
            'x-api-key',
            'x-auth-token',
            'x-csrf-token',
        ];

        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * Очистить данные сессии от чувствительной информации
     */
    private function sanitizeSessionData(array $sessionData): array
    {
        $sensitiveKeys = [
            'password',
            'token',
            'key',
            'secret',
            'auth',
            'login',
        ];

        foreach ($sessionData as $key => $value) {
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (stripos($key, $sensitiveKey) !== false) {
                    $sessionData[$key] = '[REDACTED]';
                    break;
                }
            }
        }

        return $sessionData;
    }
}
