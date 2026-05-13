import React from 'react';
import { ArrowLeft } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

const TermsPage: React.FC = () => {
  const { webApp, isTelegramMode } = useTelegram();

  const handleBack = () => {
    window.location.hash = 'profile';
  };

  return (
    <Layout>
      <div className="elderly-friendly" style={{ padding: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '24px' }}>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{ 
              width: 'auto', 
              padding: '12px', 
              marginBottom: '0',
              marginRight: '12px'
            }}
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <h1 className="elderly-title" style={{ fontSize: '24px', margin: '0', color: 'var(--text-primary)' }}>
            Пользовательское соглашение
          </h1>
        </div>

        {/* Содержимое соглашения */}
        <div className="simple-card" style={{ 
          background: 'rgba(255, 255, 255, 0.1)',
          border: '1px solid rgba(255, 255, 255, 0.2)',
          backdropFilter: 'blur(10px)'
        }}>

          <div style={{ 
            fontSize: '14px', 
            lineHeight: '1.6', 
            color: 'var(--text-primary)',
            display: 'flex',
            flexDirection: 'column',
            gap: '16px'
          }}>
            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                1. Общие положения
              </h3>
              <p style={{ margin: '0' }}>
                Настоящее Пользовательское соглашение (далее — «Соглашение») регулирует порядок использования сервиса {(import.meta as any).env.VITE_APP_NAME || 'VPN'} (далее — «Сервис»).
              </p>
              <p style={{ margin: '0' }}>
                Используя Сервис, вы подтверждаете, что прочитали и согласны с условиями настоящего Соглашения.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                2. Условия использования
              </h3>
              <p style={{ margin: '0' }}>
                Вы обязуетесь не использовать Сервис в целях, нарушающих законодательство Российской Федерации или других государств.
              </p>
              <p style={{ margin: '0' }}>
                Вы несете полную ответственность за любые действия, совершенные с использованием Сервиса.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                3. Описание Сервиса
              </h3>
              <p style={{ margin: '0' }}>
                Сервис обеспечивает конфиденциальность соединения в интернете, шифруя передаваемые данные и скрывая IP-адрес пользователя.
              </p>
              <p style={{ margin: '0' }}>
                Мы не предоставляем доступ к определённым ресурсам и не гарантируем доступ к каким-либо сторонним платформам.
              </p>
              <p style={{ margin: '0' }}>
                Сервис работает по принципу "облачной анонимизации", не отслеживает активность пользователей и не вмешивается в содержимое их интернет-трафика.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                4. Демо-период
              </h3>
              <p style={{ margin: '0' }}>
                Новый пользователь получает доступ к бесплатному демо-периоду сроком на 7 дней с момента первого входа в приложение или авторизации.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                5. Подписка и автоматическое продление
              </h3>
              <p style={{ margin: '0' }}>
                Пользователь соглашается на автоматическое продление подписки, если не отключил эту функцию вручную в разделе "Профиль → Оплата".
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                6. Изменение стоимости подписки
              </h3>
              <p style={{ margin: '0' }}>
                Мы оставляем за собой право изменять стоимость подписки. В случае повышения стоимости более чем на 10% вы будете уведомлены заранее. Изменения вступают в силу со следующего платёжного периода.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                7. Политика возврата
              </h3>
              <p style={{ margin: '0' }}>
                <strong>Условия возврата:</strong> Вы можете запросить возврат средств, если полученные услуги были некачественными или не предоставлены в соответствии с условиями.
              </p>
              <p style={{ margin: '0' }}>
                <strong>Процедура возврата:</strong> Для запроса возврата свяжитесь с нашей службой поддержки по указанным контактным данным. Мы рассмотрим ваш запрос и произведем возврат средств.
              </p>
              <p style={{ margin: '0' }}>
                <strong>Сроки возврата:</strong> Мы рассмотрим ваш запрос в течение дня. Срок исполнения возврата зависит от вашего банка.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                8. Конфиденциальность
              </h3>
              <p style={{ margin: '0' }}>
                Мы не собираем и не храним информацию о вашей онлайн-активности.
              </p>
              <p style={{ margin: '0' }}>
                Мы не передаём данные третьим лицам.
              </p>
              <p style={{ margin: '0' }}>
                Используются современные методы шифрования для защиты информации.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                9. Отказ от ответственности
              </h3>
              <p style={{ margin: '0' }}>
                Сервис предоставляется «как есть» и «по мере доступности». Мы не гарантируем его бесперебойную, безошибочную или постоянную работу.
              </p>
              <p style={{ margin: '0' }}>
                Сервис не предназначен для обхода ограничений, блокировок или доступа к запрещённым ресурсам. Мы не контролируем, как пользователь использует соединение.
              </p>
              <p style={{ margin: '0' }}>
                Пользователь несёт полную ответственность за использование сервиса и соблюдение применимого законодательства.
              </p>
              <p style={{ margin: '0' }}>
                Мы не несем ответственности за любые действия, совершённые пользователями с использованием нашего сервиса.
              </p>
            </section>

            <section>
              <h3 style={{ 
                fontSize: '16px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 8px 0'
              }}>
                10. Заключительные положения
              </h3>
              <p style={{ margin: '0' }}>
                Мы оставляем за собой право вносить изменения в настоящее Соглашение. Продолжение использования Сервиса после изменений означает согласие с ними.
              </p>
            </section>

          </div>
        </div>
      </div>
    </Layout>
  );
};

export default TermsPage;
