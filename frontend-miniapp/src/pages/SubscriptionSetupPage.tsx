import React, { useState, useEffect } from 'react';
import { ArrowLeft, Plus, Settings, ArrowRight } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

const SubscriptionSetupPage: React.FC = () => {
  const { user, subscription, webApp, isTelegramMode } = useTelegram();
  const [connectData, setConnectData] = useState<any>(null);

  // Получаем OS из URL
  const getOSFromURL = (): string => {
    const hash = window.location.hash;
    const match = hash.match(/vpn-setup\/([^\/]+)/);
    return match ? match[1] : '';
  };

  // Загружаем данные подключения (те же, что были на предыдущем шаге)
  const loadConnectData = async () => {
    const os = getOSFromURL();
    
    // Получаем токен из subscription или из localStorage
    const subscriptionToken = subscription?.token || localStorage.getItem('subscription_token');
    
    if (!os || !subscriptionToken) {
      return;
    }

    try {
      const response = await api.get(`/miniapp/connect/${os}`, {
        headers: {
          'X-Sub-Token': subscriptionToken
        }
      });

      const data = response.data;
      
      if (data.success) {
        setConnectData(data);
      }
    } catch (err) {
      console.error('Error loading connect data:', err);
    }
  };

  useEffect(() => {
    loadConnectData();

    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [subscription, user]);

  const handleBack = () => {
    window.location.hash = `connect/${connectData?.os.slug || ''}`; // Возвращаемся на страницу установки приложения
  };

  const handleAutoAdd = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    if (connectData?.auto_connect) {
      window.open(connectData.auto_connect, '_blank');
    }
  };

  const handleManualAdd = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    // Переходим к ручному добавлению подписки
    const os = getOSFromURL();
    if (os) {
      window.location.hash = `#manual-add/${os}`;
    }
  };

  const handleNext = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    window.location.hash = `vpn-success`; // Переходим к странице поздравления
  };

  const getOSIcon = (slug: string): string => {
    switch (slug) {
      case 'ios': return '📱';
      case 'android': return '🤖';
      case 'huawei': return '🌸';
      case 'mac': return '💻';
      case 'windows': return '🖥️';
      case 'android_tv': return '📺';
      default: return '📱';
    }
  };

  if (!user || !subscription) {
    return (
      <Layout>
        <div style={{ 
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'center', 
          minHeight: '50vh',
          flexDirection: 'column',
          gap: '16px'
        }}>
          <div style={{ 
            width: '40px', 
            height: '40px', 
            border: '3px solid var(--accent-yellow)', 
            borderTop: '3px solid transparent',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite'
          }}></div>
          <p className="elderly-text" style={{ color: 'var(--text-primary)' }}>
            Загрузка данных...
          </p>
        </div>
      </Layout>
    );
  }

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
            Ключ
          </h1>
        </div>

        {/* Action Buttons */}
        <div className="simple-card">
          {/* Примечание */}
          <p style={{ 
            fontSize: '16px',
            color: 'var(--text-dark-strong)',
            marginBottom: '24px',
            textAlign: 'center',
            lineHeight: '1.4',
            fontWeight: '500'
          }}>
            Добавьте ключ ВПН в приложение {connectData?.client_app?.display_name || 'VPN'} с помощью кнопки ниже
          </p>
          
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            {/* Auto Add Button - показываем только если есть auto_connect */}
            {connectData?.auto_connect && (
              <button
                onClick={handleAutoAdd}
                className="vpn-button vpn-button-primary"
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '12px',
                  fontSize: '16px'
                }}
              >
                <Plus className="w-5 h-5" />
                <span>Добавить ключ автоматически</span>
              </button>
            )}

            {/* Helpful text between buttons */}
            {connectData?.auto_connect && (
              <div style={{ 
                textAlign: 'center',
                padding: '12px',
                background: 'var(--bg-card-glass)',
                borderRadius: '12px',
                border: '1px solid var(--border-light)',
                marginBottom: '8px'
              }}>
                <p className="elderly-text" style={{ 
                  fontSize: '14px', 
                  margin: '0',
                  color: 'var(--text-dark-secondary)',
                  lineHeight: '1.4'
                }}>
                  Если автоматическое добавление не сработало, воспользуйтесь ручным способом
                </p>
              </div>
            )}

            {/* Manual Add Button - всегда показываем */}
            <button
              onClick={handleManualAdd}
              className="vpn-button vpn-button-secondary"
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '12px',
                fontSize: '16px'
              }}
            >
              <Settings className="w-5 h-5" />
              <span>Добавить ключ вручную</span>
            </button>

            {/* Next Button - всегда показываем */}
            <button
              onClick={handleNext}
              className="vpn-button vpn-button-secondary"
              style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '12px',
                fontSize: '16px'
              }}
            >
              <ArrowRight className="w-5 h-5" />
              <span>Далее</span>
            </button>
          </div>
        </div>

        {/* Footer */}
        <div style={{ textAlign: 'center', paddingBottom: '20px', marginTop: '24px' }}>
          <p style={{ 
            fontSize: '14px',
            color: 'rgba(255, 255, 255, 0.6)',
            margin: '0'
          }}>
            @{__BOT_USERNAME__}
          </p>
        </div>
      </div>
    </Layout>
  );
};

export default SubscriptionSetupPage;