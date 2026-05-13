# Система мониторинга ошибок VPN сервиса

## Обзор

Система мониторинга ошибок автоматически отслеживает и отправляет уведомления об ошибках в Telegram канал для быстрого реагирования на проблемы в VPN сервисе.

## Возможности

- 🚨 **Автоматическое отслеживание ошибок** - все исключения автоматически отправляются в Telegram
- 🔥 **Критические ошибки** - особый приоритет для серьезных проблем
- ⚠️ **Предупреждения** - уведомления о потенциальных проблемах
- 🛡️ **Безопасность** - автоматическая очистка чувствительных данных
- 📊 **Детальная информация** - контекст, трейс, информация о пользователе
- 🎯 **Гибкая настройка** - можно включать/выключать и настраивать

## Быстрый старт

### 1. Настройка Telegram канала

```bash
# Создайте канал в Telegram и добавьте бота как администратора
# Получите ID канала
curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates"
```

### 2. Настройка переменных окружения

Добавьте в `.env`:

```env
# Основные настройки Telegram (уже должны быть)
TG_BOT_TOKEN=your_bot_token_here

# Настройки мониторинга ошибок
TELEGRAM_ERROR_CHANNEL_ID=-1001234567890
TELEGRAM_ERROR_CHANNEL_NAME=@error_monitoring
TELEGRAM_ERROR_NOTIFICATIONS_ENABLED=true
```

### 3. Автоматическая настройка

```bash
# Интерактивная настройка
php artisan error-monitoring:setup

# Или с параметрами
php artisan error-monitoring:setup --channel-id=-1001234567890 --channel-name=@error_monitoring --test
```

### 4. Проверка статуса

```bash
# Проверить конфигурацию и подключение
php artisan error-monitoring:status
```

### 5. Проверка системы

```bash
# Проверить статус системы
php artisan error-monitoring:status

# Настроить систему (если нужно)
php artisan error-monitoring:setup
```

## Архитектура

### Основные компоненты

1. **ErrorNotificationService** - основной сервис для отправки уведомлений
2. **GlobalErrorHandler** - глобальный обработчик ошибок Laravel
3. **ErrorMonitoringTrait** - трейт для упрощения интеграции
4. **Команды Artisan** - для настройки и тестирования

### Интеграция в сервисы

Система уже интегрирована в ключевые сервисы:
- `PaymentService` - ошибки обработки платежей
- `SyncVpnKeyService` - ошибки синхронизации VPN ключей

### Добавление в новые сервисы

```php
use App\Traits\Error\ErrorMonitoringTrait;

class YourService
{
    use ErrorMonitoringTrait;

    public function someMethod()
    {
        try {
            // Ваш код
        } catch (Throwable $e) {
            $this->reportError($e, [
                'additional_context' => 'value',
                'user_id' => $userId,
            ]);
            
            throw $e;
        }
    }
}
```

## Типы уведомлений

### 🚨 Обычные ошибки
- Ошибки приложения
- Исключения в коде
- Ошибки валидации (в некоторых случаях)

**Пример:**
```
🚨 Ошибка в VPN сервисе

⏰ Время: 2024-01-15 14:30:25
🌍 Окружение: production
🖥️ Сервер: vpn-server-01

❌ Ошибка: Database connection failed
📁 Файл: /app/Services/Payment/PaymentService.php
📍 Строка: 245

📋 Контекст:
• service: PaymentService
• method: processPayment
• payment_id: 12345
• chat_id: 67890
```

### 🔥 Критические ошибки
- Ошибки базы данных
- Ошибки Redis
- Ошибки памяти
- Fatal errors
- HTTP ошибки 5xx

**Пример:**
```
🔥 КРИТИЧЕСКАЯ ОШИБКА! 🔥

⏰ Время: 2024-01-15 14:30:25
🌍 Окружение: production
🖥️ Сервер: vpn-server-01

💥 Ошибка: Redis connection refused
📁 Файл: /app/Services/Cache/CacheService.php
📍 Строка: 89

📋 Контекст:
• service: CacheService
• method: getCachedData
• cache_key: user:12345:subscription
```

### ⚠️ Предупреждения
- Потенциальные проблемы
- Нестандартные ситуации
- Системные предупреждения

**Пример:**
```
⚠️ Предупреждение в VPN сервисе

⏰ Время: 2024-01-15 14:30:25
🌍 Окружение: production
🖥️ Сервер: vpn-server-01

⚠️ Сообщение: High memory usage detected
```

## Безопасность

Система автоматически очищает чувствительные данные:

- Пароли и токены
- API ключи
- Данные авторизации
- Cookie
- CSRF токены

## Мониторинг и аналитика

### Информация в уведомлениях

- ⏰ Время возникновения
- 🌍 Окружение (production/staging/local)
- 🖥️ Информация о сервере
- ❌ Детали ошибки
- 📁 Файл и строка
- 📋 Контекст выполнения
- 🔗 Трейс выполнения
- 👤 Информация о пользователе (если доступна)

### Фильтрация ошибок

Система автоматически пропускает:
- Ошибки валидации
- Ошибки авторизации
- 404 ошибки в production
- Ошибки отправки уведомлений (чтобы избежать рекурсии)

## Управление

### Включение/выключение

```env
# Включить мониторинг
TELEGRAM_ERROR_NOTIFICATIONS_ENABLED=true

# Выключить мониторинг
TELEGRAM_ERROR_NOTIFICATIONS_ENABLED=false
```

### Команды управления

```bash
# Проверить статус
php artisan error-monitoring:status

# Настроить систему
php artisan error-monitoring:setup

# Протестировать
php artisan error-monitoring:test --type=error
```

## Troubleshooting

### Проблемы с подключением

1. **Проверьте токен бота:**
   ```bash
   php artisan error-monitoring:test --type=channel
   ```

2. **Проверьте права бота:**
   - Бот должен быть администратором канала
   - Бот должен иметь права на отправку сообщений

3. **Проверьте ID канала:**
   - Для каналов ID начинается с -100
   - Убедитесь, что ID правильный

### Проблемы с отправкой

1. **Проверьте логи:**
   ```bash
   tail -f storage/logs/laravel.log | grep "error notification"
   ```

2. **Проверьте конфигурацию:**
   ```bash
   php artisan error-monitoring:status
   ```

## Расширение функциональности

### Добавление новых типов уведомлений

```php
// В ErrorNotificationService
public function sendCustomNotification(string $type, string $message, array $context = []): bool
{
    // Ваша логика
}
```

### Интеграция с внешними системами

```php
// В ErrorNotificationService
private function sendToExternalSystem(Throwable $exception, array $context = []): void
{
    // Отправка в Slack, Discord, Email и т.д.
}
```

## Поддержка

При возникновении проблем:

1. Проверьте логи: `storage/logs/laravel.log`
2. Запустите диагностику: `php artisan error-monitoring:status`
3. Протестируйте подключение: `php artisan error-monitoring:test --type=channel`

## Лицензия

Система мониторинга ошибок является частью VPN сервиса и следует тем же условиям лицензирования.
