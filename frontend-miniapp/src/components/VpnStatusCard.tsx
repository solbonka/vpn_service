import React, { useState, useEffect } from 'react';
import { Shield, Wifi, WifiOff, Copy, Check } from 'lucide-react';
import api from '../services/api';

interface VpnStatusCardProps {
  status: 'ACTIVE' | 'BLOCKED' | 'EXPIRED' | null;
  subscriptionEndDate?: string;
  planName?: string;
  isConnected?: boolean;
  vpnKeyUrl?: string | null;
}

const VpnStatusCard: React.FC<VpnStatusCardProps> = ({ 
  status, 
  subscriptionEndDate, 
  planName, 
  isConnected = false,
  vpnKeyUrl
}) => {
  const [logoBase64, setLogoBase64] = useState<string | null>(null);
  const [copied, setCopied] = useState<boolean>(false);

  useEffect(() => {
    const loadLogo = async () => {
      try {
        const response = await api.get('/miniapp/logo');
        if (response.data.success) {
          setLogoBase64(response.data.logo);
        }
      } catch (error) {
        console.log('Logo load error:', error);
      }
    };

    loadLogo();
  }, []);

  // Функция для копирования ссылки в буфер обмена
  const copyToClipboard = async () => {
    if (!vpnKeyUrl) {
      return;
    }

    try {
      await navigator.clipboard.writeText(vpnKeyUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (error) {
      console.error('Failed to copy:', error);
      // Fallback для старых браузеров
      const textArea = document.createElement('textarea');
      textArea.value = vpnKeyUrl;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    }
  };
  const getStatusText = () => {
    if (!status) return { text: 'offline', color: 'var(--text-light)' };
    switch (status) {
      case 'ACTIVE':
        return { text: 'online', color: 'var(--accent-yellow)' };
      case 'BLOCKED':
        return { text: 'blocked', color: '#ef4444' };
      case 'EXPIRED':
        return { text: 'expired', color: '#f59e0b' };
      default:
        return { text: 'offline', color: 'var(--text-light)' };
    }
  };

  const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const day = date.getDate();
    const months = [
      'января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
      'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
    ];
    const month = months[date.getMonth()];
    const year = date.getFullYear();
    return `${day} ${month} ${year}`;
  };

  const statusInfo = getStatusText();

  return (
    <div style={{ 
      display: 'flex', 
      flexDirection: 'column', 
      alignItems: 'center',
      padding: '40px 20px',
      marginBottom: '30px',
      width: '100%'
    }}>
      {/* Центральный круг с VPN статусом */}
      <div style={{
        position: 'relative',
        width: '200px',
        height: '200px',
        marginBottom: '20px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}>
        {/* Внешние кольца */}
        <div style={{
          position: 'absolute',
          width: '200px',
          height: '200px',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, rgba(251, 191, 36, 0.1) 50%, transparent 70%)',
          animation: 'pulse 2s infinite'
        }} />
        <div style={{
          position: 'absolute',
          width: '160px',
          height: '160px',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0.05) 50%, transparent 70%)',
          animation: 'pulse 2s infinite 0.5s'
        }} />
        <div style={{
          position: 'absolute',
          width: '120px',
          height: '120px',
          borderRadius: '50%',
          background: 'radial-gradient(circle, rgba(251, 191, 36, 0.1) 0%, rgba(251, 191, 36, 0.03) 50%, transparent 70%)',
          animation: 'pulse 2s infinite 1s'
        }} />

        {/* Центральный логотип */}
        <div style={{
          width: '100px',
          height: '100px',
          borderRadius: '50%',
          background: 'var(--gradient-primary)',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          boxShadow: '0 8px 32px rgba(30, 64, 175, 0.3)',
          border: '3px solid var(--accent-yellow)',
          zIndex: 10,
          position: 'relative',
          overflow: 'hidden'
        }}>
          {logoBase64 ? (
            <img 
              src={logoBase64} 
              alt={`${(import.meta as any).env.VITE_APP_NAME || __APP_NAME__ || 'VPN Aginskoe'} Logo`}
              style={{
                width: '80px',
                height: '80px',
                objectFit: 'contain',
                zIndex: 11,
                borderRadius: '50%'
              }}
            />
          ) : (
            <div style={{
              width: '80px',
              height: '80px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '32px',
              color: 'white',
              zIndex: 11
            }}>
              🌐
            </div>
          )}
        </div>
      </div>

      {/* Статус и информация о подписке */}
      <div style={{
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
        width: '100%',
        maxWidth: '320px',
        marginBottom: '20px'
      }}>
        {/* Статус */}
        <div style={{
          textAlign: 'center',
          width: '100%',
          marginBottom: '8px'
        }}>
          <div style={{ 
            fontSize: '16px', 
            color: 'var(--accent-yellow)',
            fontWeight: '600',
            marginBottom: '4px'
          }}>
            Статус подписки
          </div>
          <div style={{ 
            fontSize: '16px', 
            color: 'var(--text-primary)',
            fontWeight: '500'
          }}>
            {status === 'ACTIVE' && subscriptionEndDate ? (
              <>активна до {formatDate(subscriptionEndDate)}</>
            ) : status === 'ACTIVE' ? (
              <>активна</>
            ) : (
              <>не активна</>
            )}
          </div>
        </div>

        {/* Кнопка скопировать VPN ключ */}
        {status === 'ACTIVE' && (
          <button
            onClick={copyToClipboard}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
              padding: '12px 20px',
              backgroundColor: copied ? '#10b981' : 'var(--primary-blue)',
              color: 'white',
              border: 'none',
              borderRadius: '12px',
              fontSize: '14px',
              fontWeight: '600',
              cursor: 'pointer',
              transition: 'all 0.2s ease',
              marginTop: '12px',
              boxShadow: '0 4px 12px rgba(30, 64, 175, 0.3)'
            }}
            onMouseOver={(e) => {
              if (!copied) {
                e.currentTarget.style.backgroundColor = '#1e40af';
                e.currentTarget.style.transform = 'translateY(-1px)';
                e.currentTarget.style.boxShadow = '0 6px 16px rgba(30, 64, 175, 0.4)';
              }
            }}
            onMouseOut={(e) => {
              if (!copied) {
                e.currentTarget.style.backgroundColor = 'var(--primary-blue)';
                e.currentTarget.style.transform = 'translateY(0)';
                e.currentTarget.style.boxShadow = '0 4px 12px rgba(30, 64, 175, 0.3)';
              }
            }}
          >
            {copied ? (
              <>
                <Check className="w-4 h-4" />
                Скопировано!
              </>
            ) : (
              <>
                <Copy className="w-4 h-4" />
                Скопировать ссылку на VPN ключ
              </>
            )}
          </button>
        )}

      </div>
    </div>
  );
};

export default VpnStatusCard;
