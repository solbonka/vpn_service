<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по добавлению ключ от VPN в приложение «v2RayTun» на Android</title>
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
                    <h1 class="h2 mb-2">Как добавить ключ от VPN в приложение «v2RayTun» на Android</h1>
                </div>

                <!-- Шаг 1 с подпунктами -->
                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2">
                        <div class="badge bg-primary rounded-pill align-self-start">1</div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Чтобы добавить ключ,
                                вам нужно установить приложение «v2RayTun» из GooglePlay
                                – <a href="https://play.google.com/store/apps/details?id=com.v2raytun.android"
                                     target="_blank" class="text-decoration-underline">по этой ссылке</a>.
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2">
                        <div class="badge bg-primary rounded-pill align-self-start">2</div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">Скопируйте ссылку ниже, нажав на нее.</h3>
                            <img class="img-fluid" src="{{asset('/vpn/images/subscription-v2raytun.png')}}" alt=""/>
                        </div>
                    </div>
                </div>

                <div class="list-group-item border-0 px-0">
                    <div class="d-flex gap-2">
                        <div class="badge bg-primary rounded-pill align-self-start">3</div>
                        <div class="w-100">
                            <h3 class="h6 fw-bold mb-2">
                                Перейдите в приложение v2raytun, нажмите "+", далее нажмите "Импорт из
                                буфера обмена",
                                теперь для включение VPN достаточно нажать на круглую кнопку включения и VPN
                                включится.
                                <img class="img-fluid" src="{{asset('/vpn/images/android/app.jpg')}}" alt=""/>
                            </h3>
                            <p class=" mb-2">
                                При первом подключении к VPN операционная система Android может
                                показать окно «Разрешить приложению v2raytun установить соединение с VPN?».
                                Разрешите.
                            </p>
                            <p class=" mb-2">
                                Готово, VPN подключён.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
