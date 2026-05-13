<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по установке приложения «v2RayTun» на iOS</title>
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
                    <h1 class="h2 mb-2">Установка приложения «v2RayTun» на iOS</h1>
                </div>
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2">
                        <div class="badge bg-primary rounded-pill align-self-start"></div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Установите приложение «v2RayTun»</h3>
                            <p class="mb-3 text-muted small">Перейдите в <a href="https://apps.apple.com/ru/app/v2raytun/id6476628951" target="_blank" class="text-decoration-underline">App Store</a> и скачайте приложение.</p>
                            <div class="sub-step">
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">1</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Если ссылка не открывается, запустите App Store вручную.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore.jpg')}}" alt="App Store"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">2</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Нажмите на поиск в нижней части экрана.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore2.jpg')}}" alt="Поиск в App Store"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">3</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Введите «v2RayTun» в строке поиска.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore3.jpg')}}" alt="Поиск приложения"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">4</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Нажмите на кнопку поиска.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore4.jpg')}}" alt="Кнопка поиска"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">5</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Выберите приложение «v2RayTun» и нажмите «Установить».</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore5.jpg')}}" alt="Установка приложения"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">6</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Дождитесь завершения установки.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore6.jpg')}}" alt="Процесс установки"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">7</div>
                                    <div>
                                        <p class="mb-0 text-muted small">Поздравляем! Приложение установлено. Вернитесь в <a href="{{ config('telegram.bot_link') }}" target="_blank" class="text-decoration-underline">{{ config('app.name') }}</a>, чтобы продолжить настройку.</p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/ios/appstore7.jpg')}}" alt="Приложение установлено"/>
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
