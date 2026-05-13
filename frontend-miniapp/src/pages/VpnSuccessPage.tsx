import React, { useEffect } from 'react';
import { CheckCircle } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

const VpnSuccessPage: React.FC = () => {
  const { webApp, isTelegramMode } = useTelegram();

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.ready();
      webApp.expand();
    }
  }, []);

  const handleFinish = () => {
    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('medium');
    }
    // Возвращаемся на главную страницу
    window.location.hash = '';
  };

  return (
    <Layout>
      <div style={{ 
        minHeight: '100vh',
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        justifyContent: 'center',
        padding: '40px 20px',
        position: 'relative'
      }}>
        {/* Central Success Area */}
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
          
          {/* Main success icon */}
          <div style={{
            position: 'relative',
            width: '80px',
            height: '80px',
            borderRadius: '50%',
            background: 'var(--accent-yellow)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            color: 'var(--text-dark)',
            boxShadow: 'var(--shadow-button)'
          }}>
            <CheckCircle className="w-12 h-12" />
          </div>
        </div>

        {/* Success Title */}
        <h1 style={{ 
          fontSize: '32px', 
          marginBottom: '16px',
          color: 'white',
          fontWeight: '700',
          textAlign: 'center',
          lineHeight: '1.2'
        }}>
          Поздравляем!
        </h1>

        {/* Success Description */}
        <p style={{ 
          fontSize: '18px',
          color: 'rgba(255, 255, 255, 0.8)',
          marginBottom: '48px',
          lineHeight: '1.4',
          textAlign: 'center',
          maxWidth: '300px'
        }}>
          VPN успешно настроен<br/>
          и готов к использованию
        </p>


        {/* Finish Button */}
        <button
          onClick={handleFinish}
          style={{
            width: '100%',
            maxWidth: '320px',
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
          <span>Готово</span>
        </button>

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

export default VpnSuccessPage;
