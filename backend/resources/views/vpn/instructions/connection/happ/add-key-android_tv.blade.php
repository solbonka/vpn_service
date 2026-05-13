<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Инструкция по добавлению подписки в приложение «Happ» на Android TV</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333333;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .custom-width {
            max-width: 800px;
            margin: 0 auto;
        }

        .section-card {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 2rem;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            background: linear-gradient(135deg, #00d4aa, #00b894);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .section-content {
            padding: 2rem;
        }

        .preliminary-note {
            background-color: #f8f9fa;
            border-left: 4px solid #00d4aa;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 0 8px 8px 0;
            border: 1px solid #e9ecef;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .step-number {
            background-color: #00d4aa;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }

        .step-text {
            flex: 1;
            line-height: 1.6;
            color: #333333;
        }

        .connect-instructions {
            background-color: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #00d4aa;
            border: 1px solid #e9ecef;
        }

        .connect-icon {
            width: 40px;
            height: 40px;
            background-color: #00d4aa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .connect-icon::before {
            content: "✓";
            color: white;
            font-size: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="custom-width">
        <!-- Заголовок -->
        <div class="text-center mb-4">
            <h1 class="h2 mb-2" style="color: #333333;">Инструкция по работе с Happ на Android TV</h1>
        </div>

        <!-- Раздел 1: Добавить подписку -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h7v2H7v-2z"/>
                    </svg>
                </div>
                <h2 class="section-title">Добавить подписку</h2>
            </div>
            <div class="section-content">
                <div class="preliminary-note">
                    <strong>Предварительно:</strong> У вас должна быть подписка в приложении Happ на телефоне.
                </div>

                <h4 class="mb-3" style="color: #00d4aa;">Добавление подписки на Android TV:</h4>

                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">Нажмите в приложении на телевизоре кнопку <strong>+</strong></div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">Вы получите QR код на экране телевизора</div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-text">Откройте приложение Happ на телефоне</div>
                </div>

                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-text">Нажмите <strong>+</strong> → <strong>QR-код</strong> → отсканируйте код с телевизора → подписка добавится</div>
                </div>
            </div>
        </div>

        <!-- Раздел 2: Подключите и используйте -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                </div>
                <h2 class="section-title">Подключите и используйте</h2>
            </div>
            <div class="section-content">
                <div class="connect-instructions">
                    <div class="connect-icon"></div>
                    <p class="mb-0">
                        На главной странице приложения нажмите большую кнопку включения в центре для подключения к VPN. 
                        Не забудьте выбрать сервер в списке серверов.
                    </p>
                </div>
            </div>
        </div>

        <!-- Дополнительная информация -->
        <div class="section-card">
            <div class="section-content">
                <h4 class="mb-3" style="color: #00d4aa;">Скачать Happ для Android TV:</h4>
                <p class="mb-3">
                    Установите приложение Happ из Google Play Store:
                </p>
                <a href="https://play.google.com/store/apps/details?id=com.happproxy" 
                   target="_blank" 
                   class="btn btn-success btn-lg"
                   style="background-color: #00d4aa; border-color: #00d4aa;">
                    📱 Скачать Happ для Android TV
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>