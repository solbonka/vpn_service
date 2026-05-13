<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по установке приложение «v2RayTun» на Android</title>
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
                <!-- Заголовок -->
                <div class="text-center mb-4">
                    <h1 class="h2 mb-2">Как установить приложение «v2RayTun» на Android</h1>
                </div>

                <!-- Шаг 1 с подпунктами -->
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2">
                        <div class="badge bg-primary rounded-pill align-self-start"></div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Установите приложение «v2RayTun» из Google Play
                                – <a href="https://play.google.com/store/apps/details?id=com.v2raytun.android"
                                     target="_blank" class="text-decoration-underline">по этой ссылке</a>.</h3>
                            <div class="sub-step">
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">1</div>
                                    <div>
                                        <p class="mb-0 text-muted small">
                                            Если ссылка не открывается, запустите приложение Google Play на телефоне.
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplay.jpg')}}" alt="Google Play"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">2</div>
                                    <div>
                                        <p class="mb-3 text-muted small">
                                            В Google Play нажмите на значок поиска.
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplaysearch.jpg')}}" alt="Поиск"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">3</div>
                                    <div>
                                        <p class="mb-3 text-muted small">
                                            Введите «v2RayTun» в строку поиска и нажмите поиск.
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplay3.jpg')}}" alt="Поиск v2RayTun"/>
                                  </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">4</div>
                                    <div>
                                        <p class="mb-3 text-muted small">
                                            В результатах поиска найдите «v2RayTun» и нажмите «Установить».
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplaysearch4.jpg')}}" alt="Установка"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">5</div>
                                    <div>
                                        <p class="mb-3 text-muted small">
                                            Дождитесь завершения установки.
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplaysetup.jpg')}}" alt="Процесс установки"/>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="badge bg-secondary rounded-pill align-self-start">6</div>
                                    <div>
                                        <p class="mb-3 text-muted small">
                                            Поздравляем! Приложение установлено. Вернитесь в
                                            <a href="{{ config('telegram.bot_link') }}"
                                               target="_blank" class="text-decoration-underline">{{ config('app.name') }}</a> и продолжите настройку.
                                        </p>
                                        <img class="img-fluid" src="{{asset('/vpn/images/android/googleplaysetup2.jpg')}}" alt="Успешная установка"/>
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
