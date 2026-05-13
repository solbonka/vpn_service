<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по установке приложения «v2RayTun» на Honor и Huawei</title>
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

        .step-image {
            max-width: 100%;
            height: auto;
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
                    <h1 class="h2 mb-2">Как установить приложение «v2RayTun» на Honor и Huawei</h1>
                </div>

                <!-- Шаги с подпунктами -->
                <div class="list-group">
                    <div class="list-group-item border-0 px-0">
                        <div class="d-flex gap-2">
                            <div class="badge bg-primary rounded-pill align-self-start"></div>
                            <div class="w-100">
                                <div class="sub-step">
                                    <!-- Шаг 1 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">1</div>
                                        <div>
                                            <p class="mb-0 text-muted small">Откройте приложение AppGallery на своем устройстве.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei.jpg')}}" alt="AppGallery"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 2 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">2</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Введите «v2RayTun» в строку поиска и нажмите поиск, после нажмите получить.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei2.jpg')}}" alt="Search for v2RayTun"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 3 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">3</div>
                                        <div>
                                            <p class="mb-3 text-muted small">В открывшемся окне, нажмите на кнопку установить.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei3.jpg')}}" alt="Install"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 4 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">4</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Если произошел сбой загрузки, перейдите в раздел «APK Variants».</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei8.jpg')}}" alt="APK Variants"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 5 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">5</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Выберите предыдущую версию и нажмите на неё чтобы начать загрузку.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei4.jpg')}}" alt="Select version"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 6 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">6</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Во время извлечения установочного пакета появится предупреждение о блокировке установки приложений из неизвестных источников. Перейдите в настройки, чтобы разрешить установку.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei5.jpg')}}" alt="Allow Installation"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 7 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">7</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Разрешите установку приложений и вернитесь обратно.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei6.jpg')}}" alt="Enable Apps Installation"/>
                                        </div>
                                    </div>

                                    <!-- Шаг 8 -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start">8</div>
                                        <div>
                                            <p class="mb-3 text-muted small">Нажмите установить, после чего приложение установится на устройство.</p>
                                            <img class="step-image" src="{{asset('/vpn/images/huawei/huawei7.jpg')}}" alt="Install"/>
                                        </div>
                                    </div>

                                    <!-- Завершение -->
                                    <div class="d-flex gap-2 mb-3">
                                        <div class="badge bg-secondary rounded-pill align-self-start"></div>
                                        <div>
                                            <p class="mb-3 text-muted small">Поздравляем! Приложение установлено. Вернитесь в <a href="{{ config('telegram.bot_link') }}" target="_blank" class="text-decoration-underline">{{ config('app.name') }}</a> и продолжите настройку.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- End of list-group -->
            </div>
        </div>
    </div>
</div>
</body>
</html>
