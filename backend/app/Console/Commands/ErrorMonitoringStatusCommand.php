<?php

namespace App\Console\Commands;

use App\Services\Telegram\ErrorNotificationService;
use Illuminate\Console\Command;

class ErrorMonitoringStatusCommand extends Command
{
    protected $signature = 'error-monitoring:status';

    protected $description = 'Показать статус системы мониторинга ошибок и конфигурацию';

    protected ErrorNotificationService $errorNotificationService;

    public function __construct(ErrorNotificationService $errorNotificationService)
    {
        parent::__construct();
        $this->errorNotificationService = $errorNotificationService;
    }

    public function handle(): int
    {
        $this->info('📊 Статус системы мониторинга ошибок');
        $this->newLine();

        $this->showConfiguration();

        $this->testConnection();

        return 0;
    }

    /**
     * Показать конфигурацию
     */
    private function showConfiguration(): void
    {
        $this->info('⚙️ Конфигурация:');

        $botToken = config('telegram.error_bot_token') ?: config('telegram.bot_token');
        $channelId = config('telegram.error_channel_id');
        $channelName = config('telegram.error_channel_name');
        $notificationsEnabled = config('telegram.error_notifications_enabled');
        $environmentName = config('telegram.error_environment_name') ?: 'Автогенерация';

        $this->table(
            ['Настройка', 'Значение', 'Статус'],
            [
                ['Токен бота ошибок', $botToken ? '***' . substr($botToken, -4) : 'Не настроен', $botToken ? '✅' : '❌'],
                ['ID канала', $channelId ?: 'Не настроен', $channelId ? '✅' : '❌'],
                ['Название канала', $channelName ?: 'Не настроено', $channelName ? '✅' : '❌'],
                ['Название продакшена', $environmentName, '✅'],
                ['Уведомления', $notificationsEnabled ? 'Включены' : 'Отключены', $notificationsEnabled ? '✅' : '⚠️'],
            ]
        );

        $this->newLine();
    }

    /**
     * Тестировать подключение
     */
    private function testConnection(): void
    {
        $this->info('🔗 Тестирование подключения...');

        try {
            $success = $this->errorNotificationService->testErrorChannel();

            if ($success) {
                $this->info('✅ Тест подключения успешен!');
                $this->info('Система мониторинга ошибок готова к использованию.');
            } else {
                $this->error('❌ Тест подключения не удался');
                $this->warn('Проверьте конфигурацию и попробуйте снова.');
            }
        } catch (\Exception $e) {
            $this->error('❌ Тест не удался: ' . $e->getMessage());
        }

        $this->newLine();
    }
}
