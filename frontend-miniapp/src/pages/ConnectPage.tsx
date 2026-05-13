import React, { useState } from 'react';
import { ArrowLeft, ChevronDown, Download, Copy, Check } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { API_BASE_URL } from '../services/api';
import api from '../services/api';

type OS = 'ios' | 'mac' | 'android' | 'android_tv' | 'windows' | 'huawei';

interface OSOption {
  id: OS;
  name: string;
  icon: string;
}

const osOptions: OSOption[] = [
  { id: 'ios', name: 'iOS (iPhone/iPad)', icon: '📱' },
  { id: 'mac', name: 'macOS', icon: '💻' },
  { id: 'android', name: 'Android', icon: '🤖' },
  { id: 'android_tv', name: 'Android TV', icon: '📺' },
  { id: 'windows', name: 'Windows', icon: '🪟' },
  { id: 'huawei', name: 'Huawei || Honor', icon: '🌸' }
];

const instructions: Record<OS, {
  title: string;
  steps: string[];
  downloadLinks?: { name: string; url: string }[];
}> = {
  ios: {
    title: 'Настройка VPN на iOS',
    steps: [
      '1. Скачайте WireGuard из App Store',
      '2. Откройте приложение WireGuard',
      '3. Нажмите "+" для добавления туннеля',
      '4. Выберите "Create from file" или "Add empty tunnel"',
      '5. Вставьте VPN конфигурацию в поле',
      '6. Нажмите "Save" и активируйте туннель'
    ],
    downloadLinks: [
      { name: 'WireGuard в App Store', url: 'https://apps.apple.com/app/wireguard/id1441195209' }
    ]
  },
  mac: {
    title: 'Настройка VPN на macOS',
    steps: [
      '1. Скачайте WireGuard с официального сайта',
      '2. Установите приложение WireGuard',
      '3. Откройте WireGuard и нажмите "+"',
      '4. Выберите "Create from file" или "Create empty tunnel"',
      '5. Вставьте VPN конфигурацию',
      '6. Нажмите "Save" и активируйте туннель'
    ],
    downloadLinks: [
      { name: 'WireGuard для macOS', url: 'https://www.wireguard.com/install/' }
    ]
  },
  android: {
    title: 'Настройка VPN на Android',
    steps: [
      '1. Скачайте WireGuard из Google Play Store',
      '2. Откройте приложение WireGuard',
      '3. Нажмите "+" для добавления туннеля',
      '4. Выберите "Create from file" или "Add empty tunnel"',
      '5. Вставьте VPN конфигурацию в поле',
      '6. Нажмите "Save" и активируйте туннель'
    ],
    downloadLinks: [
      { name: 'WireGuard в Google Play', url: 'https://play.google.com/store/apps/details?id=com.wireguard.android' }
    ]
  },
  'android-tv': {
    title: 'Настройка VPN на Android TV',
    steps: [
      '1. Установите WireGuard на Android TV',
      '2. Откройте приложение WireGuard',
      '3. Используйте пульт для навигации',
      '4. Добавьте новый туннель',
      '5. Вставьте VPN конфигурацию',
      '6. Активируйте туннель'
    ],
    downloadLinks: [
      { name: 'WireGuard для Android TV', url: 'https://play.google.com/store/apps/details?id=com.wireguard.android' }
    ]
  },
  windows: {
    title: 'Настройка VPN на Windows',
    steps: [
      '1. Скачайте WireGuard с официального сайта',
      '2. Установите программу WireGuard',
      '3. Откройте WireGuard и нажмите "Add Tunnel"',
      '4. Выберите "Add from file" или "Add empty tunnel"',
      '5. Вставьте VPN конфигурацию в поле "Private Key"',
      '6. Нажмите "Save" и затем "Activate" для подключения'
    ],
    downloadLinks: [
      { name: 'WireGuard для Windows', url: 'https://www.wireguard.com/install/' }
    ]
  },
  huawei: {
    title: 'Настройка VPN на Huawei || Honor',
    steps: [
      '1. Скачайте WireGuard из AppGallery',
      '2. Откройте приложение WireGuard',
      '3. Нажмите "+" для добавления туннеля',
      '4. Выберите "Create from file" или "Add empty tunnel"',
      '5. Вставьте VPN конфигурацию в поле',
      '6. Нажмите "Save" и активируйте туннель'
    ],
    downloadLinks: [
      { name: 'WireGuard в AppGallery', url: 'https://appgallery.huawei.com/app/C100000000' }
    ]
  }
};

const ConnectPage: React.FC = () => {
  const { user, subscription, webApp, isTelegramMode } = useTelegram();
  const [selectedOS, setSelectedOS] = useState<OS | null>(null);
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);
  const [vpnKey, setVpnKey] = useState<string>('');
  const [isLoading, setIsLoading] = useState(false);
  const [copied, setCopied] = useState(false);
  const [connectData, setConnectData] = useState<any>(null);
  const [isLoadingData, setIsLoadingData] = useState(false);
  const [showCopyModal, setShowCopyModal] = useState(false);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = '';
  };

  const handleOSSelect = async (os: OS) => {
    setSelectedOS(os);
    setIsDropdownOpen(false);
    
    // Загружаем данные для выбранной ОС
    await loadConnectData(os);
  };

  const loadConnectData = async (os: OS) => {
    setIsLoadingData(true);
    setConnectData(null);
    setVpnKey('');
    
    try {
      const headers: Record<string, string> = {};

      // Добавляем токен подписки, если есть
      if (subscription?.token) {
        headers['X-Sub-Token'] = subscription.token;
      }

      const response = await api.get(`/miniapp/connect/${os}`, {
        headers
      });
      
      const data = response.data;
      setConnectData(data);
      
      // Используем VPN ключ из API, если есть
      if (data.vpn_key) {
        setVpnKey(data.vpn_key);
      } else if (subscription?.token) {
        // Fallback на моковый ключ, если API не вернул ключ
        setVpnKey(`[Interface]
PrivateKey = ${subscription.token}
Address = 10.0.0.2/24
DNS = 8.8.8.8

[Peer]
PublicKey = server_public_key_here
Endpoint = vpn.example.com:51820
AllowedIPs = 0.0.0.0/0`);
      }
      
    } catch (error) {
      console.error('Error loading connect data:', error);
      
      // Fallback на моковый ключ при ошибке
      if (subscription?.token) {
        setVpnKey(`[Interface]
PrivateKey = ${subscription.token}
Address = 10.0.0.2/24
DNS = 8.8.8.8

[Peer]
PublicKey = server_public_key_here
Endpoint = vpn.example.com:51820
AllowedIPs = 0.0.0.0/0`);
      }
    } finally {
      setIsLoadingData(false);
    }
  };

  // Убираем handleAutoConnect - он вызывает проблемы

  const copyToClipboard = async () => {
    try {
      // В Telegram Mini App используем специальный метод
      if (webApp && isTelegramMode) {
        // Пробуем современный API
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(vpnKey);
        } else {
          // Fallback для Telegram Mini App
          const textArea = document.createElement('textarea');
          textArea.value = vpnKey;
          textArea.style.position = 'fixed';
          textArea.style.left = '-999999px';
          textArea.style.top = '-999999px';
          textArea.style.opacity = '0';
          textArea.style.pointerEvents = 'none';
          document.body.appendChild(textArea);
          
          // Фокусируемся и выделяем
          textArea.focus();
          textArea.select();
          textArea.setSelectionRange(0, 99999); // Для мобильных устройств
          
          const successful = document.execCommand('copy');
          document.body.removeChild(textArea);
          
          if (!successful) {
            throw new Error('execCommand failed');
          }
        }
        
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
        
        // Показываем уведомление в Telegram
        webApp.showAlert('VPN ключ скопирован в буфер обмена!');
        
      } else {
        // Обычный браузер
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(vpnKey);
        } else {
          const textArea = document.createElement('textarea');
          textArea.value = vpnKey;
          textArea.style.position = 'fixed';
          textArea.style.left = '-999999px';
          textArea.style.top = '-999999px';
          document.body.appendChild(textArea);
          textArea.focus();
          textArea.select();
          document.execCommand('copy');
          document.body.removeChild(textArea);
        }
        
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
        alert('VPN ключ скопирован в буфер обмена!');
      }
      
    } catch (err) {
      console.error('Failed to copy: ', err);
      
      // Показываем модальное окно для ручного копирования
      setShowCopyModal(true);
    }
  };

  const generateVpnKey = async () => {
    setIsLoading(true);
    try {
      // Временная имитация - в реальности здесь будет API запрос
      setTimeout(() => {
        if (subscription?.token) {
          setVpnKey(`[Interface]
PrivateKey = ${subscription.token}
Address = 10.0.0.2/24
DNS = 8.8.8.8

[Peer]
PublicKey = server_public_key_here
Endpoint = vpn.example.com:51820
AllowedIPs = 0.0.0.0/0`);
        }
        setIsLoading(false);
      }, 1000);
    } catch (error) {
      console.error('Error generating VPN key:', error);
      setIsLoading(false);
    }
  };

  React.useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [webApp, isTelegramMode]);

  // Убираем этот useEffect, так как данные загружаются через loadConnectData
  // React.useEffect(() => {
  //   if (selectedOS && !vpnKey) {
  //     generateVpnKey();
  //   }
  // }, [selectedOS]);

  // Убираем отладочные логи

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
          <div>
            <h1 className="elderly-title" style={{ fontSize: '24px', margin: '0', color: 'var(--text-primary)' }}>
              Подключение к VPN
            </h1>
            <p className="elderly-text" style={{ fontSize: '14px', margin: '4px 0 0 0', color: 'var(--text-secondary)' }}>
              Выберите вашу операционную систему для получения инструкций
            </p>
          </div>
        </div>

        {/* Выпадающий список ОС */}
        <div style={{ marginBottom: '24px' }}>
          <label className="elderly-text-large" style={{ 
            display: 'block', 
            color: 'var(--text-primary)', 
            fontWeight: '600', 
            marginBottom: '12px' 
          }}>
            Выберите операционную систему:
          </label>
          <div style={{ position: 'relative' }}>
            <button
              onClick={() => setIsDropdownOpen(!isDropdownOpen)}
              className="simple-card"
              style={{
                width: '100%',
                padding: '16px',
                cursor: 'pointer',
                border: '2px solid var(--border-light)',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                background: 'var(--bg-card-glass)',
                backdropFilter: 'blur(10px)'
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                {selectedOS ? (
                  <>
                    <span style={{ fontSize: '24px' }}>{osOptions.find(os => os.id === selectedOS)?.icon}</span>
                    <span className="elderly-text-large" style={{ color: 'var(--text-dark)' }}>
                      {osOptions.find(os => os.id === selectedOS)?.name}
                    </span>
                  </>
                ) : (
                  <span className="elderly-text" style={{ color: 'var(--text-dark-secondary)' }}>
                    Выберите ОС...
                  </span>
                )}
              </div>
              <ChevronDown className={`w-6 h-6 transition-transform ${isDropdownOpen ? 'rotate-180' : ''}`} 
                style={{ color: 'var(--text-dark-secondary)' }} />
            </button>

            {isDropdownOpen && (
              <div style={{
                position: 'absolute',
                top: '100%',
                left: '0',
                right: '0',
                marginTop: '8px',
                background: 'var(--bg-card-glass)',
                border: '2px solid var(--border-light)',
                borderRadius: '16px',
                boxShadow: 'var(--shadow-card)',
                backdropFilter: 'blur(15px)',
                zIndex: 50,
                maxHeight: '240px',
                overflowY: 'auto'
              }}>
                {osOptions.map((os) => (
                  <button
                    key={os.id}
                    onClick={() => handleOSSelect(os.id)}
                    style={{
                      width: '100%',
                      padding: '16px',
                      display: 'flex',
                      alignItems: 'center',
                      gap: '12px',
                      background: 'transparent',
                      border: 'none',
                      cursor: 'pointer',
                      transition: 'background 0.2s ease'
                    }}
                    onMouseEnter={(e) => e.currentTarget.style.background = 'var(--accent-yellow)'}
                    onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
                  >
                    <span style={{ fontSize: '24px' }}>{os.icon}</span>
                    <span className="elderly-text-large" style={{ color: 'var(--text-dark)' }}>{os.name}</span>
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Инструкции и VPN ключ */}
        {selectedOS && (
          <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
            {isLoadingData ? (
              <div style={{ 
                display: 'flex', 
                alignItems: 'center', 
                justifyContent: 'center', 
                padding: '32px' 
              }}>
                <div style={{ 
                  width: '32px', 
                  height: '32px', 
                  border: '2px solid var(--accent-yellow)', 
                  borderTop: '2px solid transparent',
                  borderRadius: '50%',
                  animation: 'spin 1s linear infinite'
                }}></div>
                <span className="elderly-text" style={{ marginLeft: '12px', color: 'var(--text-secondary)' }}>
                  Загружаем данные...
                </span>
              </div>
            ) : connectData ? (
              <>
                {/* VPN ключ */}
                <div className="simple-card">
                  <div style={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'space-between', 
                    marginBottom: '16px' 
                  }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
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
                        <span style={{ fontSize: '20px' }}>🔑</span>
                      </div>
                      <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark)' }}>
                        Ваш VPN ключ:
                      </h3>
                    </div>
                    <button
                      onClick={copyToClipboard}
                      disabled={isLoading}
                      className="vpn-button vpn-button-secondary"
                      style={{ 
                        width: 'auto', 
                        padding: '8px 16px', 
                        marginBottom: '0',
                        fontSize: '14px'
                      }}
                    >
                      <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        {copied ? <Check className="w-4 h-4" /> : <Copy className="w-4 h-4" />}
                        {copied ? 'Скопировано' : 'Копировать'}
                      </div>
                    </button>
                  </div>
                  <div className="glass-card" style={{ padding: '16px' }}>
                    {isLoading ? (
                      <div style={{ 
                        display: 'flex', 
                        alignItems: 'center', 
                        justifyContent: 'center', 
                        padding: '32px' 
                      }}>
                        <div style={{ 
                          width: '32px', 
                          height: '32px', 
                          border: '2px solid var(--accent-yellow)', 
                          borderTop: '2px solid transparent',
                          borderRadius: '50%',
                          animation: 'spin 1s linear infinite'
                        }}></div>
                        <span className="elderly-text" style={{ marginLeft: '12px', color: 'var(--text-dark-secondary)' }}>
                          Генерируем VPN ключ...
                        </span>
                      </div>
                    ) : (
                      <pre 
                        style={{
                          color: '#10b981',
                          fontSize: '14px',
                          whiteSpace: 'pre-wrap',
                          fontFamily: 'monospace',
                          background: 'var(--bg-card)',
                          padding: '12px',
                          borderRadius: '8px',
                          border: '1px solid var(--border-light)',
                          userSelect: 'all',
                          cursor: 'text',
                          wordBreak: 'break-all',
                          margin: '0'
                        }}
                        onClick={(e) => {
                          // Выделяем весь текст при клике
                          const range = document.createRange();
                          range.selectNodeContents(e.currentTarget);
                          const selection = window.getSelection();
                          selection?.removeAllRanges();
                          selection?.addRange(range);
                        }}
                      >
                        {vpnKey}
                      </pre>
                    )}
                  </div>
                </div>

                {/* Ссылки для скачивания */}
                {connectData.download_links && connectData.download_links.length > 0 && (
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
                        <Download className="w-6 h-6" />
                      </div>
                      <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark)' }}>
                        Скачать приложение:
                      </h3>
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                      {connectData.download_links.map((link: any, index: number) => (
                        <a
                          key={index}
                          href={link.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="vpn-button vpn-button-primary"
                        >
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <Download className="w-5 h-5" />
                            <span>{link.name}</span>
                          </div>
                        </a>
                      ))}
                    </div>
                  </div>
                )}

                {/* Инструкции */}
                {connectData.instructions && (
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
                        <span style={{ fontSize: '20px' }}>📋</span>
                      </div>
                      <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark)' }}>
                        Инструкции:
                      </h3>
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                      {connectData.instructions.setup && (
                        <a
                          href={connectData.instructions.setup.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="vpn-button vpn-button-secondary"
                        >
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <span style={{ fontSize: '18px' }}>📱</span>
                            <span>{connectData.instructions.setup.title}</span>
                          </div>
                        </a>
                      )}
                      {connectData.instructions.connection && (
                        <a
                          href={connectData.instructions.connection.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="vpn-button vpn-button-secondary"
                        >
                          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                            <span style={{ fontSize: '18px' }}>🔑</span>
                            <span>{connectData.instructions.connection.title}</span>
                          </div>
                        </a>
                      )}
                    </div>
                  </div>
                )}

                {/* Убираем кнопку автоматического подключения - она вызывает проблемы */}
              </>
            ) : (
              <div className="simple-card" style={{ textAlign: 'center', padding: '32px' }}>
                <p className="elderly-text" style={{ color: 'var(--text-dark-secondary)', marginBottom: '16px' }}>
                  Ошибка загрузки данных
                </p>
                <button
                  onClick={() => selectedOS && loadConnectData(selectedOS)}
                  className="vpn-button vpn-button-secondary"
                  style={{ width: 'auto', marginBottom: '0' }}
                >
                  Попробовать снова
                </button>
              </div>
            )}
          </div>
        )}

        {/* Совет */}
        <div className="simple-card" style={{ marginTop: '32px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '12px' }}>
            <div style={{ 
              width: '40px', 
              height: '40px', 
              background: 'var(--gradient-yellow)', 
              borderRadius: '50%', 
              display: 'flex', 
              alignItems: 'center', 
              justifyContent: 'center',
              color: 'var(--text-dark)',
              boxShadow: 'var(--shadow-button)'
            }}>
              <span style={{ fontSize: '18px' }}>💡</span>
            </div>
            <h3 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark)' }}>
              Совет
            </h3>
          </div>
          <p className="elderly-text" style={{ color: 'var(--text-dark-secondary)', lineHeight: '1.6' }}>
            Выберите вашу операционную систему, скопируйте VPN ключ и следуйте пошаговым инструкциям для подключения.
          </p>
        </div>
      </div>

      {/* Модальное окно для копирования */}
      {showCopyModal && (
        <div style={{
          position: 'fixed',
          top: '0',
          left: '0',
          right: '0',
          bottom: '0',
          background: 'rgba(0, 0, 0, 0.5)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 50,
          padding: '16px'
        }}>
          <div className="simple-card" style={{ maxWidth: '400px', width: '100%' }}>
            <h3 className="elderly-subtitle" style={{ marginBottom: '16px', color: 'var(--text-dark)' }}>
              Скопируйте VPN ключ
            </h3>
            <p className="elderly-text" style={{ marginBottom: '16px', color: 'var(--text-dark-secondary)' }}>
              Выделите текст ниже и скопируйте его вручную:
            </p>
            <div className="glass-card" style={{ marginBottom: '16px', padding: '16px' }}>
              <pre style={{
                color: '#10b981',
                fontSize: '12px',
                whiteSpace: 'pre-wrap',
                fontFamily: 'monospace',
                wordBreak: 'break-all',
                userSelect: 'all',
                margin: '0'
              }}>
                {vpnKey}
              </pre>
            </div>
            <div style={{ display: 'flex', gap: '12px' }}>
              <button
                onClick={() => setShowCopyModal(false)}
                className="vpn-button vpn-button-secondary"
                style={{ flex: 1, marginBottom: '0' }}
              >
                Закрыть
              </button>
              <button
                onClick={() => {
                  setShowCopyModal(false);
                  copyToClipboard();
                }}
                className="vpn-button vpn-button-primary"
                style={{ flex: 1, marginBottom: '0' }}
              >
                Попробовать снова
              </button>
            </div>
          </div>
        </div>
      )}
    </Layout>
  );
};

export default ConnectPage;