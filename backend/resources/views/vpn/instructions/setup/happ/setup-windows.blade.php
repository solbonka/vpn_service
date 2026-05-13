<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по установке приложения «Happ» на Windows</title>
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
                    <h1 class="h2 mb-2">Установка приложения «Happ» на Windows</h1>
                </div>
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2 mb-4">
                        <div class="badge bg-primary rounded-pill align-self-start"></div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Выполните все шаги для установки приложения Happ.</h3>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">1</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Скачайте приложение Happ по
                                            <a href="https://github.com/Happ-proxy/happ-desktop/releases/latest/download/setup-Happ.x86.exe"
                                               target="_blank" class="text-decoration-underline">этой ссылке</a>.
                                        </h3>
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
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/happ/happ1.jpg')}}" alt="Установочный файл"/>
                                    </div>
                                </div>
                            </div>

                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">3</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            Если антивирус или ОС выдаст предупреждение, проигнорируйте его — это стандартная проверка. Нажмите  запустить или установить, и продолжите установку.
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/happ/happ2.jpg')}}" alt="Предупреждение антивируса"/>
                                    </div>
                                </div>
                            </div>


                            <div class="list-group-item border-0 px-0 mb-4">
                                <div class="d-flex gap-2">
                                    <div class="badge bg-primary rounded-pill align-self-start">4</div>
                                    <div class="w-100">
                                        <h3 class="h6 fw-bold mb-2">
                                            После установки приложение будет доступно на вашем устройстве.
                                        </h3>
                                        <img class="img-fluid" src="{{asset('/vpn/images/windows/setup/happ/happ3.jpg')}}" alt="Запущенное приложение"/>
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
