import React, { useState, useEffect } from 'react';
import { ArrowLeft, Monitor, Smartphone, Tablet, Tv } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

interface DeviceInfo {
  type: 'desktop' | 'mobile' | 'tablet' | 'unknown';
  os: string;
  name: string;
}

interface OS {
  id: string;
  name: string;
  icon: string;
  category: 'desktop' | 'mobile' | 'tv';
}

const osOptions: OS[] = [
  { id: 'ios', name: 'iOS (iPhone/iPad)', icon: '📱', category: 'mobile' },
  { id: 'mac', name: 'macOS', icon: '💻', category: 'desktop' },
  { id: 'windows', name: 'Windows', icon: '🪟', category: 'desktop' },
  { id: 'huawei', name: 'Huawei || Honor', icon: '🌸', category: 'mobile' },
  { id: 'android', name: 'Android', icon: '🤖', category: 'mobile' },
  { id: 'android_tv', name: 'Android TV', icon: '📺', category: 'tv' }
  // Linux временно убран, так как не поддерживается в бэкенде
];

const DeviceSelectionPage: React.FC = () => {
  const { user, webApp, isTelegramMode } = useTelegram();
  const [deviceInfo, setDeviceInfo] = useState<DeviceInfo | null>(null);
  const [selectedOS, setSelectedOS] = useState<string | null>(null);
  const [showOSSelection, setShowOSSelection] = useState(false);

  const handleBack = () => {
    window.location.hash = '';
  };

  const handleContinueOnDevice = () => {
    if (deviceInfo && deviceInfo.os) {
      // Переходим к установке приложения с определенной ОС
      window.location.hash = `connect/${deviceInfo.os}`;
    } else {
      // Показываем выбор ОС
      setShowOSSelection(true);
    }
  };

  const handleSelectDifferentDevice = () => {
    setShowOSSelection(true);
  };

  const handleOSSelect = (osId: string) => {
    setSelectedOS(osId);
    window.location.hash = `connect/${osId}`;
  };

  const detectDevice = (): DeviceInfo => {
    const userAgent = navigator.userAgent.toLowerCase();
    
    // Более точное определение iOS
    if (/iphone/.test(userAgent) || /ipod/.test(userAgent)) {
      return { type: 'mobile', os: 'ios', name: 'iOS (iPhone)' };
    } else if (/ipad/.test(userAgent)) {
      return { type: 'mobile', os: 'ios', name: 'iOS (iPad)' };
    } 
    // Android должен быть перед другими проверками
    else if (/android/.test(userAgent)) {
      return { type: 'mobile', os: 'android', name: 'Android' };
    } 
    // Windows
    else if (/windows/.test(userAgent)) {
      return { type: 'desktop', os: 'windows', name: 'Windows' };
    } 
    // macOS - проверяем более точно
    else if (/mac os x/.test(userAgent) || /macintosh/.test(userAgent)) {
      return { type: 'desktop', os: 'mac', name: 'macOS' };
    } 
    // Linux (но не Android) - пока не поддерживается в бэкенде
    else if (/linux/.test(userAgent) && !/android/.test(userAgent)) {
      return { type: 'unknown', os: '', name: 'Неизвестное устройство' };
    } 
    // Huawei HarmonyOS
    else if (/harmonyos/.test(userAgent) || /huawei/.test(userAgent)) {
      return { type: 'mobile', os: 'huawei', name: 'Huawei || Honor' };
    } 
    else {
      return { type: 'unknown', os: '', name: 'Неизвестное устройство' };
    }
  };

  const getDeviceIcon = (type: string) => {
    switch (type) {
      case 'desktop': return <Monitor className="w-16 h-16" />;
      case 'mobile': return <Smartphone className="w-16 h-16" />;
      case 'tablet': return <Tablet className="w-16 h-16" />;
      default: return <Monitor className="w-16 h-16" />;
    }
  };

  const getOSIcon = (osId: string) => {
    const os = osOptions.find(o => o.id === osId);
    return os ? os.icon : '💻';
  };

  useEffect(() => {
    const detected = detectDevice();
    setDeviceInfo(detected);
    
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

  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  if (showOSSelection) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px' }}>
          {/* Header */}
          <div style={{ display: 'flex', alignItems: 'center', marginBottom: '24px' }}>
            <button
              onClick={() => setShowOSSelection(false)}
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
              Выберите устройство
            </h1>
          </div>

          {/* OS Selection */}
          <div className="simple-card">
            <h3 className="elderly-subtitle" style={{ 
              marginBottom: '20px', 
              color: 'var(--text-dark-strong)',
              textAlign: 'center'
            }}>
              Выберите операционную систему
            </h3>
            
            <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
              {osOptions.map(os => (
                <button
                  key={os.id}
                  onClick={() => handleOSSelect(os.id)}
                  style={{
                    padding: '16px',
                    borderRadius: '12px',
                    cursor: 'pointer',
                    transition: 'all 0.3s ease',
                    background: 'var(--bg-card-glass)',
                    border: '2px solid var(--border-light)',
                    boxShadow: 'var(--shadow-card)',
                    backdropFilter: 'blur(10px)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '16px'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = 'var(--gradient-yellow)';
                    e.currentTarget.style.borderColor = 'var(--accent-yellow)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = 'var(--bg-card-glass)';
                    e.currentTarget.style.borderColor = 'var(--border-light)';
                  }}
                >
                  <span style={{ fontSize: '32px' }}>{os.icon}</span>
                  <span className="elderly-text-large" style={{ color: 'var(--text-dark-strong)' }}>
                    {os.name}
                  </span>
                </button>
              ))}
            </div>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div style={{ 
        minHeight: '100vh',
        padding: '0',
        position: 'relative'
      }}>
        {/* Header */}
        <div style={{ 
          display: 'flex', 
          alignItems: 'center', 
          padding: '16px 20px',
          background: 'rgba(255, 255, 255, 0.1)',
          backdropFilter: 'blur(10px)',
          borderBottom: '1px solid rgba(255, 255, 255, 0.1)'
        }}>
          <button
            onClick={handleBack}
            style={{
              background: 'transparent',
              border: 'none',
              color: 'white',
              padding: '8px',
              borderRadius: '8px',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              transition: 'background 0.2s ease'
            }}
            onMouseEnter={(e) => e.currentTarget.style.background = 'rgba(255, 255, 255, 0.1)'}
            onMouseLeave={(e) => e.currentTarget.style.background = 'transparent'}
          >
            <ArrowLeft className="w-6 h-6" />
          </button>
          <h1 style={{ 
            fontSize: '20px', 
            margin: '0 0 0 16px', 
            color: 'white',
            fontWeight: '600'
          }}>
            {(import.meta as any).env.VITE_APP_NAME || __APP_NAME__ || 'VPN Aginskoe'}
          </h1>
        </div>

        {/* Main Content */}
        <div style={{ 
          flex: 1,
          display: 'flex',
          flexDirection: 'column',
          alignItems: 'center',
          justifyContent: 'center',
          padding: '40px 20px',
          position: 'relative'
        }}>
          {/* Central Connection Area */}
          <div style={{ 
            position: 'relative',
            width: '200px',
            height: '200px',
            marginBottom: '32px',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center'
          }}>
            {/* Outer radiating circles */}
            <div style={{
              position: 'absolute',
              width: '100%',
              height: '100%',
              borderRadius: '50%',
              background: 'var(--accent-yellow)',
              opacity: 0.15,
              animation: 'pulse 2s infinite ease-in-out'
            }} />
            <div style={{
              position: 'absolute',
              width: '160px',
              height: '160px',
              borderRadius: '50%',
              background: 'var(--accent-yellow)',
              opacity: 0.1,
              animation: 'pulse 2s infinite ease-in-out',
              animationDelay: '0.5s'
            }} />
            <div style={{
              position: 'absolute',
              width: '120px',
              height: '120px',
              borderRadius: '50%',
              background: 'var(--accent-yellow)',
              opacity: 0.05,
              animation: 'pulse 2s infinite ease-in-out',
              animationDelay: '1s'
            }} />
            
            {/* Main connection icon */}
            <div style={{
              position: 'relative',
              width: '80px',
              height: '80px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              color: 'white'
            }}>
              {deviceInfo ? getDeviceIcon(deviceInfo.type) : <Monitor className="w-12 h-12" />}
            </div>
          </div>


          {/* Description */}
          <p style={{ 
            fontSize: '18px',
            color: 'rgba(255, 255, 255, 0.8)',
            marginBottom: '48px',
            lineHeight: '1.4',
            textAlign: 'center',
            maxWidth: '300px'
          }}>
            Настройка VPN происходит<br/>
            в 3 шага и занимает пару минут
          </p>

          {/* Action Buttons */}
          <div style={{ 
            width: '100%',
            maxWidth: '320px',
            display: 'flex', 
            flexDirection: 'column', 
            gap: '16px' 
          }}>
            {deviceInfo && deviceInfo.os ? (
              <>
                {/* Primary button - Continue on this device */}
                <button
                  onClick={handleContinueOnDevice}
                  style={{
                    width: '100%',
                    padding: '20px 24px',
                    background: 'var(--accent-yellow)',
                    border: 'none',
                    borderRadius: '16px',
                    color: 'var(--text-dark-strong)',
                    fontSize: '18px',
                    fontWeight: '600',
                    cursor: 'pointer',
                    transition: 'all 0.3s ease',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: '12px',
                    boxShadow: '0 8px 24px rgba(251, 191, 36, 0.3)'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = 'var(--light-yellow)';
                    e.currentTarget.style.transform = 'translateY(-2px)';
                    e.currentTarget.style.boxShadow = '0 12px 32px rgba(251, 191, 36, 0.4)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = 'var(--accent-yellow)';
                    e.currentTarget.style.transform = 'translateY(0)';
                    e.currentTarget.style.boxShadow = '0 8px 24px rgba(251, 191, 36, 0.3)';
                  }}
                >
                  <span style={{ fontSize: '20px' }}>{getOSIcon(deviceInfo.os)}</span>
                  <span>Начать настройку на вашем {deviceInfo.name}</span>
                </button>

                {/* Secondary button - Install on another device */}
                <button
                  onClick={handleSelectDifferentDevice}
                  style={{
                    width: '100%',
                    padding: '20px 24px',
                    background: 'rgba(255, 255, 255, 0.1)',
                    border: '2px solid rgba(255, 255, 255, 0.2)',
                    borderRadius: '16px',
                    color: 'white',
                    fontSize: '18px',
                    fontWeight: '600',
                    cursor: 'pointer',
                    transition: 'all 0.3s ease',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: '12px',
                    backdropFilter: 'blur(10px)'
                  }}
                  onMouseEnter={(e) => {
                    e.currentTarget.style.background = 'rgba(255, 255, 255, 0.2)';
                    e.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                    e.currentTarget.style.transform = 'translateY(-2px)';
                  }}
                  onMouseLeave={(e) => {
                    e.currentTarget.style.background = 'rgba(255, 255, 255, 0.1)';
                    e.currentTarget.style.borderColor = 'rgba(255, 255, 255, 0.2)';
                    e.currentTarget.style.transform = 'translateY(0)';
                  }}
                >
                  <span style={{ fontSize: '20px' }}>📱</span>
                  <span>Установить на другом устройстве</span>
                </button>
              </>
            ) : (
              /* Unknown device - show device selection */
              <button
                onClick={handleSelectDifferentDevice}
                style={{
                  width: '100%',
                  padding: '20px 24px',
                  background: 'var(--accent-yellow)',
                  border: 'none',
                  borderRadius: '16px',
                  color: 'var(--text-dark-strong)',
                  fontSize: '18px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  transition: 'all 0.3s ease',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '12px',
                  boxShadow: '0 8px 24px rgba(251, 191, 36, 0.3)'
                }}
                onMouseEnter={(e) => {
                  e.currentTarget.style.background = 'var(--light-yellow)';
                  e.currentTarget.style.transform = 'translateY(-2px)';
                  e.currentTarget.style.boxShadow = '0 12px 32px rgba(251, 191, 36, 0.4)';
                }}
                onMouseLeave={(e) => {
                  e.currentTarget.style.background = 'var(--accent-yellow)';
                  e.currentTarget.style.transform = 'translateY(0)';
                  e.currentTarget.style.boxShadow = '0 8px 24px rgba(251, 191, 36, 0.3)';
                }}
              >
                <span style={{ fontSize: '20px' }}>💻</span>
                <span>Выбрать устройство</span>
              </button>
            )}
          </div>
        </div>

        {/* Footer */}
        <div style={{ 
          position: 'absolute',
          bottom: '20px',
          left: '50%',
          transform: 'translateX(-50%)',
          textAlign: 'center'
        }}>
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

export default DeviceSelectionPage;
