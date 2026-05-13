import React from 'react';
import { ArrowLeft, User, FileCheck } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';

const ProfilePage: React.FC = () => {
  const { user } = useTelegram();

  const handleBack = () => {
    window.location.hash = '';
  };

  const handleTerms = () => {
    window.location.hash = 'terms';
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
            Профиль
          </h1>
        </div>

        {/* Информация о пользователе */}
        <div className="simple-card" style={{ 
          marginBottom: '20px',
          background: 'rgba(255, 255, 255, 0.1)',
          border: '1px solid rgba(255, 255, 255, 0.2)',
          backdropFilter: 'blur(10px)'
        }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
            <div style={{
              width: '60px',
              height: '60px',
              borderRadius: '50%',
              background: 'var(--gradient-primary)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '24px',
              color: 'white'
            }}>
              <User className="w-8 h-8" />
            </div>
            <div style={{ flex: 1 }}>
              <h2 style={{ 
                fontSize: '20px', 
                fontWeight: '600', 
                color: 'var(--text-primary)',
                margin: '0 0 4px 0'
              }}>
                {user?.first_name || 'Пользователь'}
                {user?.last_name && ` ${user.last_name}`}
              </h2>
              <p style={{ 
                fontSize: '14px', 
                color: 'var(--text-secondary)',
                margin: '0 0 2px 0'
              }}>
                @{user?.username || 'username'}
              </p>
              <p style={{ 
                fontSize: '12px', 
                color: 'var(--text-secondary)',
                margin: '0'
              }}>
                ID: {user?.id || 'Неизвестно'}
              </p>
            </div>
          </div>
        </div>

        {/* Пользовательское соглашение */}
        <button
          onClick={handleTerms}
          className="vpn-button vpn-button-secondary"
          style={{ 
            width: '100%'
          }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <FileCheck className="w-5 h-5" />
            <span>Пользовательское соглашение</span>
          </div>
        </button>
      </div>
    </Layout>
  );
};

export default ProfilePage;
