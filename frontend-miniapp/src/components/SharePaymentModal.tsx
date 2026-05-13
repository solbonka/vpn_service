import React, { useState } from 'react';
import { X, Copy, CheckCircle, Send } from 'lucide-react';
import { useTelegram } from '../hooks/useTelegram';

interface SharePaymentModalProps {
  isOpen: boolean;
  onClose: () => void;
  shareUrl: string;
}

const SharePaymentModal: React.FC<SharePaymentModalProps> = ({
  isOpen,
  onClose,
  shareUrl
}) => {
  const [copied, setCopied] = useState(false);
  const { webApp, isTelegramMode } = useTelegram();

  if (!isOpen) return null;

  const handleCopyLink = () => {
    navigator.clipboard.writeText(shareUrl);
    setCopied(true);
    
    if (webApp && isTelegramMode) {
      webApp.showPopup({
        title: 'Скопировано!',
        message: 'Ссылка скопирована в буфер обмена',
        buttons: [{ type: 'ok' }]
      });
    }
    
    setTimeout(() => setCopied(false), 3000);
  };

  const handleShareToTelegram = () => {
    if (webApp && isTelegramMode) {
      const shareText = 'Помоги мне оплатить VPN подписку! 🙏';
      webApp.openTelegramLink(
        `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(shareText)}`
      );
    } else {
      handleCopyLink();
    }
  };

  return (
    <div 
      style={{
        position: 'fixed',
        top: 0,
        left: 0,
        right: 0,
        bottom: 0,
        backgroundColor: 'rgba(0, 0, 0, 0.7)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        zIndex: 1000,
        padding: '20px'
      }}
      onClick={onClose}
    >
      <div
        style={{
          backgroundColor: 'var(--bg-primary, #1a1a2e)',
          borderRadius: '16px',
          padding: '24px',
          maxWidth: '400px',
          width: '100%',
          boxShadow: '0 20px 60px rgba(0, 0, 0, 0.5)'
        }}
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div style={{ 
          display: 'flex', 
          justifyContent: 'space-between', 
          alignItems: 'center', 
          marginBottom: '20px' 
        }}>
          <h2 style={{ margin: 0, fontSize: '20px', color: 'var(--text-primary, #ffffff)', fontWeight: 'bold' }}>
            Поделиться оплатой
          </h2>
          <button
            onClick={onClose}
            style={{
              background: 'none',
              border: 'none',
              cursor: 'pointer',
              padding: '4px',
              display: 'flex',
              alignItems: 'center',
              color: 'var(--text-secondary, #9ca3af)'
            }}
          >
            <X size={24} />
          </button>
        </div>

        {/* Description */}
        <p style={{ 
          color: 'var(--text-secondary, #9ca3af)', 
          fontSize: '14px', 
          marginBottom: '20px',
          lineHeight: '1.5'
        }}>
          Скопируйте ссылку и отправьте тому, кто оплатит вашу подписку. 
          Ссылка действительна 7 дней.
        </p>

        {/* Link Display */}
        <div style={{
          backgroundColor: 'var(--bg-secondary, #16213e)',
          borderRadius: '12px',
          padding: '12px',
          marginBottom: '16px',
          wordBreak: 'break-all',
          fontSize: '13px',
          color: 'var(--text-primary, #ffffff)',
          fontFamily: 'monospace',
          border: '1px solid rgba(255, 255, 255, 0.1)'
        }}>
          {shareUrl}
        </div>

        {/* Buttons */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '10px' }}>
          <button
            onClick={handleCopyLink}
            style={{
              backgroundColor: copied ? '#10b981' : 'var(--primary-blue, #2196f3)',
              color: 'white',
              border: 'none',
              borderRadius: '12px',
              padding: '14px',
              fontSize: '16px',
              fontWeight: '600',
              cursor: 'pointer',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '8px',
              transition: 'background-color 0.3s'
            }}
          >
            {copied ? (
              <>
                <CheckCircle size={20} />
                Скопировано!
              </>
            ) : (
              <>
                <Copy size={20} />
                Копировать ссылку
              </>
            )}
          </button>

          {isTelegramMode && (
            <button
              onClick={handleShareToTelegram}
              style={{
                backgroundColor: 'var(--bg-secondary, #16213e)',
                color: 'var(--text-primary, #ffffff)',
                border: '1px solid rgba(255, 255, 255, 0.2)',
                borderRadius: '12px',
                padding: '14px',
                fontSize: '16px',
                fontWeight: '600',
                cursor: 'pointer',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: '8px',
                transition: 'background-color 0.3s'
              }}
            >
              <Send size={20} />
              Отправить в Telegram
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export default SharePaymentModal;


