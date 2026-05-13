import React, { useState, useEffect } from 'react';
import { ArrowLeft, MessageCircle, Mail, Globe, HelpCircle, Search } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

const HelpPage: React.FC = () => {
  const { user, webApp, isTelegramMode, supportChannel } = useTelegram();
  const [paymentEnabled, setPaymentEnabled] = useState<boolean | null>(null);

  useEffect(() => {
    // Загружаем информацию о платежах
    loadPaymentInfo();
  }, []);

  const loadPaymentInfo = async () => {
    try {
      const response = await api.get('/miniapp/subscription/plans');
      const data = response.data;
      setPaymentEnabled(data.payment_enabled);
    } catch (err) {
      console.error('Error loading payment info:', err);
    }
  };

  const handleBack = () => {
    window.location.hash = '';
  };

  const handleContactSupport = () => {
    // Убираем @ из начала, если есть
    const channelName = supportChannel.startsWith('@') ? supportChannel.slice(1) : supportChannel;
    const supportUrl = `https://t.me/${channelName}`;
    
    if (webApp && isTelegramMode) {
      webApp.openTelegramLink(supportUrl);
    } else {
      window.open(supportUrl, '_blank');
    }
  };

  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  return (
    <Layout>
      <div className="elderly-friendly" style={{ padding: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '24px' }}>
          <button
            onClick={handleBack}
            className="simple-button-secondary"
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
            Помощь
          </h1>
        </div>

        {/* Main Help Content */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
                 {/* Need Help Section */}
                 <div className="glass-card" style={{ textAlign: 'center' }}>
                   <h2 className="elderly-subtitle" style={{ marginBottom: '8px', color: 'var(--text-dark-strong)' }}>
                     Нужна помощь?
                   </h2>
                   <p className="elderly-text" style={{ color: 'var(--text-dark)' }}>
                     Поддержка 24/7 — мы всегда готовы помочь!
                   </p>
                 </div>

          {/* Support Contact */}
          <div className="simple-card">
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
              <div style={{ 
                width: '48px', 
                height: '48px', 
                background: 'var(--gradient-yellow)', 
                borderRadius: '12px', 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                color: 'var(--text-dark)',
                boxShadow: 'var(--shadow-button)'
              }}>
                <MessageCircle className="w-6 h-6" />
              </div>
              <div>
                       <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark-strong)' }}>
                         Связаться с поддержкой
                       </h3>
                       <p className="elderly-text" style={{ fontSize: '14px', margin: '0', color: 'var(--text-dark)' }}>
                         Если у Вас возникли вопросы по подключению
                       </p>
              </div>
            </div>
            
            {/* Support Channel Button */}
            <button
              onClick={handleContactSupport}
              className="vpn-button vpn-button-primary"
              style={{ 
                marginBottom: '16px',
                position: 'relative',
                overflow: 'hidden'
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                <MessageCircle className="w-5 h-5" />
                <span style={{ fontWeight: '600' }}>{supportChannel}</span>
              </div>
            </button>

            {/* Additional Info */}
            <div style={{ 
              padding: '12px',
              background: 'var(--bg-card-glass)',
              borderRadius: '8px',
              border: '1px solid var(--border-light)'
            }}>
              <p className="elderly-text" style={{ 
                fontSize: '13px', 
                margin: '0', 
                color: 'var(--text-dark-secondary)',
                textAlign: 'center'
              }}>
                Нажмите на кнопку выше для перехода в Telegram канал поддержки
              </p>
            </div>
          </div>

          {/* Troubleshooting Section */}
          <div className="simple-card">
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' }}>
              <div style={{ 
                width: '48px', 
                height: '48px', 
                background: 'var(--gradient-yellow)', 
                borderRadius: '12px', 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center',
                color: 'var(--text-dark)',
                boxShadow: 'var(--shadow-button)'
              }}>
                <Search className="w-6 h-6" />
              </div>
                     <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark-strong)' }}>
                       А пока попробуйте:
                     </h3>
            </div>
            
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <div style={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: '12px', 
                padding: '16px',
                background: 'var(--bg-card-glass)',
                borderRadius: '12px',
                backdropFilter: 'blur(10px)',
                border: '1px solid var(--border-light)'
              }}>
                <div style={{ 
                  width: '36px', 
                  height: '36px', 
                  background: 'var(--gradient-yellow)', 
                  borderRadius: '50%', 
                  display: 'flex', 
                  alignItems: 'center', 
                  justifyContent: 'center',
                  color: 'var(--text-dark)',
                  fontSize: '16px',
                  fontWeight: '600',
                  boxShadow: 'var(--shadow-button)'
                }}>
                  1
                </div>
                       <span className="elderly-text-large" style={{ color: 'var(--text-dark-strong)' }}>
                         Перезапустить приложение
                       </span>
              </div>
              
              <div style={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: '12px', 
                padding: '16px',
                background: 'var(--bg-card-glass)',
                borderRadius: '12px',
                backdropFilter: 'blur(10px)',
                border: '1px solid var(--border-light)'
              }}>
                <div style={{ 
                  width: '36px', 
                  height: '36px', 
                  background: 'var(--gradient-yellow)', 
                  borderRadius: '50%', 
                  display: 'flex', 
                  alignItems: 'center', 
                  justifyContent: 'center',
                  color: 'var(--text-dark)',
                  fontSize: '16px',
                  fontWeight: '600',
                  boxShadow: 'var(--shadow-button)'
                }}>
                  2
                </div>
                       <span className="elderly-text-large" style={{ color: 'var(--text-dark-strong)' }}>
                         Проверить настройки подключения
                       </span>
                     </div>
                     
                     <div style={{ 
                       display: 'flex', 
                       alignItems: 'center', 
                       gap: '12px', 
                       padding: '16px',
                       background: 'var(--bg-card-glass)',
                       borderRadius: '12px',
                       backdropFilter: 'blur(10px)',
                       border: '1px solid var(--border-light)'
                     }}>
                       <div style={{ 
                         width: '36px', 
                         height: '36px', 
                         background: 'var(--gradient-yellow)', 
                         borderRadius: '50%', 
                         display: 'flex', 
                         alignItems: 'center', 
                         justifyContent: 'center',
                         color: 'var(--text-dark)',
                         fontSize: '16px',
                         fontWeight: '600',
                         boxShadow: 'var(--shadow-button)'
                       }}>
                         3
                       </div>
                       <span className="elderly-text-large" style={{ color: 'var(--text-dark-strong)' }}>
                         Убедиться, что подписка активна
                       </span>
              </div>
            </div>
          </div>

          {/* Additional Help */}
          <div className="simple-card">
                   <h3 className="elderly-subtitle" style={{ marginBottom: '16px', color: 'var(--text-dark-strong)' }}>
                     Дополнительная помощь
                   </h3>
            
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              <div style={{ 
                padding: '16px',
                background: 'var(--bg-card-glass)',
                borderRadius: '8px',
                border: '1px solid var(--border-light)'
              }}>
                <h4 className="elderly-text-large" style={{ marginBottom: '8px', color: 'var(--text-dark-strong)' }}>
                  Как подключиться к VPN?
                </h4>
                <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark)' }}>
                  1. Убедитесь, что у вас есть активная подписка<br/>
                  2. Нажмите кнопку "Подключиться"<br/>
                  3. Выберите вашу операционную систему<br/>
                  4. Следуйте инструкциям
                </p>
              </div>

              <div style={{ 
                padding: '16px',
                background: 'var(--bg-card-glass)',
                borderRadius: '8px',
                border: '1px solid var(--border-light)'
              }}>
                <h4 className="elderly-text-large" style={{ marginBottom: '8px', color: 'var(--text-dark-strong)' }}>
                  Как продлить подписку?
                </h4>
                {paymentEnabled === null ? (
                  <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark)' }}>
                    Загрузка...
                  </p>
                ) : paymentEnabled ? (
                  <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark)' }}>
                    1. Нажмите кнопку "Продлить подписку"<br/>
                    2. Выберите подходящий тариф<br/>
                    3. Оплатите подписку<br/>
                    4. Подписка активируется автоматически
                  </p>
                ) : (
                  <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark)' }}>
                    1. Нажмите кнопку "Продлить подписку"<br/>
                    2. Подписка активируется автоматически
                  </p>
                )}
              </div>

              <div style={{ 
                padding: '16px',
                background: 'var(--bg-card-glass)',
                borderRadius: '8px',
                border: '1px solid var(--border-light)'
              }}>
                <h4 className="elderly-text-large" style={{ marginBottom: '8px', color: 'var(--text-dark-strong)' }}>
                  VPN не подключается
                </h4>
                <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark)' }}>
                  • Проверьте интернет-соединение<br/>
                  • Убедитесь, что подписка активна<br/>
                  • Попробуйте другой сервер<br/>
                  • Перезапустите приложение
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default HelpPage;
