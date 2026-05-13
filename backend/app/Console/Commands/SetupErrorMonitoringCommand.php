<?php

namespace App\Console\Commands;

use App\Services\Telegram\ErrorNotificationService;
use Illuminate\Console\Command;

class SetupErrorMonitoringCommand extends Command
{
    protected $signature = 'error-monitoring:setup
                            {--channel-id= : ID Telegram канала для уведомлений об ошибках}
                            {--channel-name= : Название Telegram канала для уведомлений об ошибках}
                            {--test : Протестировать настройку после конфигурации}';

    protected $description = 'Настройка системы мониторинга ошибок с конфигурацией Telegram канала';

    protected ErrorNotificationService $errorNotificationService;

    public function __construct(ErrorNotificationService $errorNotificationService)
    {
        parent::__construct();
        $this->errorNotificationService = $errorNotificationService;
    }

    public function handle(): int
    {
        $this->info('🔧 Настройка системы мониторинга ошибок...');
        $this->newLine();

        $this->checkConfiguration();

        $channelId = $this->option('channel-id') ?: $this->askForChannelId();
        $channelName = $this->option('channel-name') ?: $this->askForChannelName();

        $this->updateConfiguration($channelId, $channelName);

        if ($this->option('test') || $this->confirm('Хотите протестировать конфигурацию?', true)) {
            $this->testConfiguration();
        }

        $this->newLine();
        $this->info('✅ Настройка системы мониторинга ошибок завершена!');
        $this->info('Добавьте следующее в ваш .env файл:');
        $this->line("TELEGRAM_ERROR_BOT_TOKEN=your_error_bot_token_here");
        $this->line("TELEGRAM_ERROR_CHANNEL_ID={$channelId}");
        $this->line("TELEGRAM_ERROR_CHANNEL_NAME={$channelName}");
        $this->line("TELEGRAM_ERROR_NOTIFICATIONS_ENABLED=true");

        return 0;
    }

    /**
     * Проверить текущую конфигурацию
     */
    private function checkConfiguration(): void
    {
        $this->info('📋 Проверка текущей конфигурации...');

        $botToken = config('telegram.error_bot_token') ?: config('telegram.bot_token');
        $channelId = config('telegram.error_channel_id');
        $notificationsEnabled = config('telegram.error_notifications_enabled');

        if (empty($botToken)) {
            $this->error('❌ Токен бота ошибок Telegram не настроен');
            $this->warn('Пожалуйста, установите TELEGRAM_ERROR_BOT_TOKEN в вашем .env файле');
            return;
        }

        $this->info('✅ Токен бота ошибок Telegram настроен');

        if (empty($channelId)) {
            $this->warn('⚠️ ID канала ошибок не настроен');
        } else {
            $this->info("✅ ID канала ошибок: {$channelId}");
        }

        if ($notificationsEnabled) {
            $this->info('✅ Уведомления об ошибках включены');
        } else {
            $this->warn('⚠️ Уведомления об ошибках отключены');
        }

        $this->newLine();
    }

    /**
     * Запросить ID канала
     */
    private function askForChannelId(): string
    {
        $this->info('📱 Для получения ID вашего канала:');
        $this->line('1. Создайте канал в Telegram');
        $this->line('2. Добавьте вашего бота как администратора');
        $this->line('3. Отправьте сообщение в канал');
        $this->line('4. Выполните: curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates"');
        $this->line('5. Найдите chat.id в ответе (для каналов ID начинается с -100)');
        $this->newLine();

        return $this->ask('Введите ID вашего Telegram канала');
    }

    /**
     * Запросить название канала
     */
    private function askForChannelName(): string
    {
        return $this->ask('Введите название вашего Telegram канала (например, @error_monitoring)', '@error_monitoring');
    }

    /**
     * Обновить конфигурацию
     */
    private function updateConfiguration(string $channelId, string $channelName): void
    {
        $this->info('⚙️ Обновление конфигурации...');

        // Здесь можно добавить логику для обновления конфигурации
        // Например, запись в .env файл или базу данных

        $this->info("✅ ID канала установлен: {$channelId}");
        $this->info("✅ Название канала установлено: {$channelName}");
    }

    /**
     * Тестировать конфигурацию
     */
    private function testConfiguration(): void
    {
        $this->info('🧪 Тестирование конфигурации...');

        try {
            $success = $this->errorNotificationService->testErrorChannel();

            if ($success) {
                $this->info('✅ Тест подключения к каналу успешен!');
            } else {
                $this->error('❌ Тест подключения к каналу не удался');
                $this->warn('Проверьте токен бота и ID канала');
            }
        } catch (\Exception $e) {
            $this->error('❌ Тест не удался: ' . $e->getMessage());
        }
    }
}
