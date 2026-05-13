# VPN Сервис

Проект состоит из бэкенда на Laravel и фронтенда на React.js, контейнеризированных с помощью Docker.

## Структура проекта

```
.
├── backend/           # Laravel бэкенд
├── frontend/         # React.js фронтенд
├── docker/           # Конфигурационные файлы Docker
│   └── nginx/       # Конфигурация Nginx
└── docker-compose.yml
```

## Требования

- Docker
- Docker Compose

---

## Начало работы

1. Клонируйте репозиторий
2. См. инструкцию ниже

---

### Скопируйте файлы .env

```bash
cp .env.example .env
cp backend/.env.example backend/.env
```

---

### Настройки backend/.env

```bash
nano backend/.env
```

- Необходимо заполнить следующие поля, подготовив канал (тут надо назначить бота админом) , бот, канал-хелпер

```
APP_NAME=Laravel #тут можно указать свое, если слова будут раздельные, то обернуть в двойные кавычки и писать через 
нижнее подчеркивание ("TEST_TEST")
APP_ENV=local #тут должно быть production для прода
APP_DEBUG=true #тут должно быть false для прода
APP_URL=https://www.domain.ru #тут указать свой

TELEGRAM_DOMAIN=https://www.domain.rup #тут указать свой

DB_DATABASE=vpn_db #привести к такому виду (если меняется тут, то меняется в корне проекта .env)
DB_USERNAME=vpn_user #привести к такому виду (если меняется тут, то меняется в корне проекта .env)
DB_PASSWORD=vpn_password #привести к такому виду (если меняется тут, то меняется в корне проекта .env)

TELEGRAM_SHOW_AUTHOR_BUTTON=true #заменить на false (кнопка об авторе показываться не будет)
CHECK_SUBSCRIPTION_TO_CHANEL=false #заменить на true

TELEGRAM_SUPPORT_CHANEL_NAME=@your_support_channel #заменить на свой

TELEGRAM_CHANEL_NAME=@testvpnchanelbot #заменить на свой
TELEGRAM_CHANEL_LINK=t.me/testvpnchanelbot #заменить на свой

SANCTUM_STATEFUL_DOMAINS=www.domain:8088,domain:8085 #заменить domain на свои
```

---

### Запустите сервисы

```bash
docker compose up -d
```

---

## Локальная разработка

### 1. Установите NGROK_AUTH_TOKEN

Откройте файл `.env` (в корне проекта) и добавьте туда ваш Ngrok токен:

```env
NGROK_AUTH_TOKEN=ваш_токен_от_ngrok
```

> Получить токен можно после регистрации на [ngrok.com](https://ngrok.com/)

---

### 2. Запустите сервисы

```bash
docker compose --profile dev up
```

---

### 3. Получите публичный URL от Ngrok

Откройте в браузере:

```
http://localhost:4040
```

Скопируйте `https://...ngrok-free.app` адрес, например:

```
https://5420-194-164-35-77.ngrok-free.app
```

---

### 4. Обновите `APP_URL` и `TELEGRAM_DOMAIN` в backend

Откройте файл `backend/.env` и установите:

```env
APP_URL=https://5420-194-164-35-77.ngrok-free.app
TELEGRAM_DOMAIN=https://5420-194-164-35-77.ngrok-free.app
```

---

### Подключитесь к контейнеру `php`

```bash
docker compose exec php bash
для прода:
docker compose exec --user root php bash
```

---

### Выполните команды внутри контейнера

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan telegraph:new-bot
```

Во время создания бота укажите:

- Токен Telegram-бота
- Имя (любое)
- Далее дважды нажмите `Enter` (если не хотите заполнять доп. поля)

---

### Установите webhook

```bash
php artisan telegraph:set-webhook
```

### Настройте Laravel Passport

```bash
php artisan passport:keys

php artisan passport:client --password
```

> Везде Enter

---

### Подключитесь к БД и настройте данные

- Для прода можно подключиться используя в PhpStorm domain, указав порт 54327

```
Заменить данные в таблицах на свои:
- servers
- plan_server (ко всем тарифам из таблицы plans, должны быть подключены все сервера
  пример: Тариф базовый и пробный должен быть у всех серверов, предположим их 2, значит:
  1 - 1
  1 - 2
  2 - 1
  2 - 2
  
  и тд.
- authot_infos (заменить на свое, если готово и включить true в backend/.env)
- vpn_configurations (тут очень важно вставить private_key, public_key, short_ids
  сгенерированные на ПЕРВОМ сервер-меин
- users (создать админ-юзера password должен быть захеширован) - для доступа к админ панели на фронте
```

✅ Готово! Бот подключён, Laravel работает, webhook настроен.

---

3. Доступ к сервисам:
    - Бэкенд API: http://localhost:8088
    - Фронтенд: http://localhost:8085
    - RabbitMQ Management: http://localhost:15672
        - Логин: vpn_user
        - Пароль: vpn_password
    - PostgreSQL: localhost:5432
        - База данных: vpn_db
        - Пользователь: vpn_user
        - Пароль: vpn_password

## Сервисы

- Nginx: Веб-сервер (Порт 80)
- PHP-FPM 8.3: Обработка PHP
- PostgreSQL: База данных (Порт 5432)
- RabbitMQ: Брокер сообщений (Порты 5672, 15672)
- React.js Frontend: (Порт 8085)

## Разработка

### Бэкенд (Laravel)

- Код бэкенда должен находиться в директории `backend`
- Laravel будет доступен по адресу http://localhost:8088

### Фронтенд (React.js)

- Код фронтенда должен находиться в директории `frontend`
- React.js будет доступен по адресу http://localhost:8085