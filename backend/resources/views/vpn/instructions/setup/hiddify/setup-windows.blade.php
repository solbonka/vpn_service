<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по установке приложения «Hiddify» на Windows</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sub-step {
            padding-left: 2.5rem;
        }
        .list-group-item:not(:last-child) {
            margin-bottom: 0.5rem;
        }
        .custom-width {
            max-width: 800px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="custom-width">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h1 class="h2 mb-2">Установка приложения «Hiddify» на Windows</h1>
                </div>
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2 mb-4">
                        <div class="badge bg-primary rounded-pill align-self-start"></div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Выполните все шаги для установки приложения Hiddify.</h3>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">1</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Скачайте приложение Hiddify по
                                            <a href="https://github.com/hiddify/hiddify-app/releases/download/v2.5.7/Hiddify-Windows-Setup-x64.exe"
                                               target="_blank" class="text-decoration-underline">этой ссылке</a>.
                                        </h3>
                                        <p class="mb-3 text-muted small">
                                            Если ссылка не работает, воспользуйтесь альтернативными источниками:
                                        </p>
                                        <ul class="mb-3 text-muted small ps-3">
                                            <li>
                                                <a href="https://apps.microsoft.com/detail/9pdfnl3qv2s5?hl=en-us&gl=RU"
                                                   target="_blank" class="text-decoration-underline">Microsoft Store</a>
                                            </li>
                                            <li>
                                                <a href="https://hiddify.com/"
                                                   target="_blank" class="text-decoration-underline">Официальный сайт</a>
                                            </li>
                                            <li>
                                                <a href="https://github.com/hiddify/hiddify-app"
                                                   target="_blank" class="text-decoration-underline">GitHub-репозиторий</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">2</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Установочный файл появится в папке «Загрузки». Откройте его.
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/hiddify.jpg')}}" alt="Установочный файл"/>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">3</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Если антивирус выдаст предупреждение, проигнорируйте его — это стандартная проверка.
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/hiddify2.jpg')}}" alt="Предупреждение антивируса"/>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">4</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            В открывшемся окне нажмите «Установить».
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/hiddify3.jpg')}}" alt="Окно установки"/>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">5</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            После установки приложение запустится автоматически.
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/hiddify4.jpg')}}" alt="Запущенное приложение"/>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start"></div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Поздравляем! Приложение установлено. Перейдите в
                                            <a href="{{ config('telegram.bot_link') }}" target="_blank" class="text-decoration-underline">{{ config('app.name') }}</a>
                                            для завершения настройки.
                                        </h3>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
