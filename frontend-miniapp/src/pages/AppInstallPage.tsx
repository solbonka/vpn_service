import React, { useState, useEffect } from 'react';
import { ArrowLeft, Download, ArrowRight, ExternalLink } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

interface DownloadLink {
  type: string;
  url: string;
  name: string;
}

interface ConnectData {
  os: {
    id: number;
    name: string;
    slug: string;
  };
  client_app: {
    id: number;
    name: string;
    display_name: string;
  };
  download_links: DownloadLink[];
  instructions: {
    setup: {
      url: string;
    };
    connection: {
      title: string;
      url: string;
    };
  };
  auto_connect?: string;
  vpn_key?: string;
}

const AppInstallPage: React.FC = () => {
  const { user, subscription, webApp, isTelegramMode } = useTelegram();
  const [connectData, setConnectData] = useState<ConnectData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [pendingDownloadUrl, setPendingDownloadUrl] = useState<string>('');

  // Получаем OS из URL
  const getOSFromURL = (): string => {
    const hash = window.location.hash;
    const match = hash.match(/connect\/([^\/]+)/);
    return match ? match[1] : '';
  };

  const loadConnectData = async () => {
    const os = getOSFromURL();
    
    if (!os) {
      setError('ОС не определена');
      setIsLoading(false);
      return;
    }

    if (!subscription?.token) {
      setError('Токен подписки не найден');
      setIsLoading(false);
      return;
    }

    try {
      const response = await api.get(`/miniapp/connect/${os}`, {
        headers: {
          'X-Sub-Token': subscription.token
        }
      });

      const data = response.data;
      
      if (data.success) {
        setConnectData(data);
      } else {
        setError(`Ошибка загрузки данных: ${data.error || 'Неизвестная ошибка'}`);
      }
    } catch (err: any) {
      if (err.response?.status === 404) {
        setError(`ОС "${os}" не поддерживается или не найдена в системе`);
      } else if (err.response?.status === 401) {
        setError('Ошибка авторизации. Проверьте токен подписки');
      } else {
        setError(`Ошибка загрузки данных подключения: ${err.response?.data?.error || err.message}`);
      }
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    // Загружаем данные только если у нас есть пользователь и подписка
    if (user && subscription) {
      loadConnectData();
    }

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
    window.location.hash = 'device-selection';
  };

  const handleDownload = (url: string) => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    setPendingDownloadUrl(url);
    setShowModal(true);
  };

  const handleConfirmDownload = () => {
    setShowModal(false);
    window.open(pendingDownloadUrl, '_blank');
    setPendingDownloadUrl('');
  };


  const handleNext = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    
    // Проверяем, что данные загружены
    if (!connectData?.os?.slug) {
      console.error('Connect data not loaded yet');
      setError('Данные еще загружаются, попробуйте еще раз');
      return;
    }
    
    // Переходим к шагу добавления подписки
    window.location.hash = `vpn-setup/${connectData.os.slug}`;
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

  const getAppIcon = (appName: string): string => {
    switch (appName.toLowerCase()) {
      case 'v2raytun': return '🔧';
      case 'hiddify': return '🛡️';
      case 'happ': return '⚡';
      default: return '📱';
    }
  };

  if (!user) {
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
            Загрузка данных пользователя...
          </p>
        </div>
      </Layout>
    );
  }

  if (!subscription) {
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
            Загрузка данных подписки...
          </p>
        </div>
      </Layout>
    );
  }

  if (isLoading) {
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
            Загружаем данные...
          </p>
        </div>
      </Layout>
    );
  }

  if (error || !connectData) {
    return (
      <Layout>
        <div className="simple-card" style={{ textAlign: 'center', padding: '40px 20px' }}>
          <h2 className="elderly-subtitle" style={{ color: 'var(--text-dark-strong)', marginBottom: '16px' }}>
            Ошибка
          </h2>
          <p className="elderly-text" style={{ color: 'var(--text-dark)', marginBottom: '24px' }}>
            {error || 'Не удалось загрузить данные'}
          </p>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
          >
            <ArrowLeft className="w-5 h-5" />
            <span>Назад</span>
          </button>
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
            Установка приложения
          </h1>
        </div>




        {/* Download Links */}
        {connectData.download_links.length > 0 && (
          <div className="simple-card">
            {/* Примечание */}
            <p style={{ 
              fontSize: '14px',
              color: 'var(--text-dark-secondary)',
              marginBottom: '20px',
              textAlign: 'center',
              lineHeight: '1.4'
            }}>
              Установите приложение {connectData.client_app.display_name} и вернитесь к этому экрану
            </p>
            
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {connectData.download_links.map((link, index) => {
                // Определяем текст кнопки в зависимости от типа ссылки
                const getButtonText = (linkName: string) => {
                  if (linkName.toLowerCase().includes('app store')) {
                    if (linkName.toLowerCase().includes('россия') || linkName.toLowerCase().includes('russia')) {
                      return 'Установить приложение в App Store Россия';
                    } else if (linkName.toLowerCase().includes('глобальная') || linkName.toLowerCase().includes('global')) {
                      return 'Установить приложение в App Store Global';
                    } else {
                      return 'Установить приложение в App Store';
                    }
                  } else if (linkName.toLowerCase().includes('play store') || linkName.toLowerCase().includes('google play')) {
                    return 'Установить приложение в Play Store';
                  } else if (linkName.toLowerCase().includes('huawei') || linkName.toLowerCase().includes('app gallery')) {
                    return 'Установить приложение в App Gallery';
                  } else {
                    return 'Установить приложение';
                  }
                };

                return (
                  <button
                    key={index}
                    onClick={() => handleDownload(link.url)}
                    className="vpn-button vpn-button-primary"
                    style={{
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: '12px',
                      fontSize: '16px'
                    }}
                  >
                    <Download className="w-5 h-5" />
                    <span>{getButtonText(link.name)}</span>
                    <ExternalLink className="w-4 h-4" />
                  </button>
                );
              })}
            </div>
          </div>
        )}

        {/* Next Button */}
        <button
          onClick={handleNext}
          className="vpn-button vpn-button-secondary"
          style={{
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            gap: '12px',
            fontSize: '16px',
            marginTop: '16px'
          }}
        >
          <span>Далее</span>
          <ArrowRight className="w-5 h-5" />
        </button>
      </div>

      {/* Modal */}
      {showModal && (
        <div style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          bottom: 0,
          backgroundColor: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000,
          padding: '20px'
        }}>
          <div style={{
            backgroundColor: 'white',
            borderRadius: '16px',
            padding: '24px',
            maxWidth: '320px',
            width: '100%',
            textAlign: 'center',
            boxShadow: '0 20px 40px rgba(0, 0, 0, 0.3)'
          }}>
            <h3 style={{
              fontSize: '20px',
              fontWeight: '600',
              color: '#1f2937',
              marginBottom: '16px'
            }}>
              Важно!
            </h3>
            <p style={{
              fontSize: '16px',
              color: '#6b7280',
              marginBottom: '24px',
              lineHeight: '1.5'
            }}>
              После установки приложения обязательно вернитесь к этому экрану для продолжения настройки VPN.
            </p>
            <div style={{
              display: 'flex',
              justifyContent: 'center'
            }}>
              <button
                onClick={handleConfirmDownload}
                style={{
                  padding: '12px 24px',
                  backgroundColor: 'var(--accent-yellow)',
                  border: 'none',
                  borderRadius: '8px',
                  color: '#1f2937',
                  fontSize: '16px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'background-color 0.2s ease',
                  width: '100%'
                }}
                onMouseEnter={(e) => e.currentTarget.style.backgroundColor = 'var(--light-yellow)'}
                onMouseLeave={(e) => e.currentTarget.style.backgroundColor = 'var(--accent-yellow)'}
              >
                Хорошо
              </button>
            </div>
          </div>
        </div>
      )}
    </Layout>
  );
};

export default AppInstallPage;
