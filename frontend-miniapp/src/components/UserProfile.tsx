import React from 'react';
import { User as UserIcon, Calendar } from 'lucide-react';

interface UserProfileProps {
  user: {
    id: number;
    first_name: string;
    last_name?: string;
    username?: string;
    language_code?: string;
    is_premium?: boolean;
  };
  subscription?: {
    id: number;
    status: string;
    end_date: string;
    plan: string;
    duration: number;
    token: string;
  } | null;
}

const UserProfile: React.FC<UserProfileProps> = ({ user, subscription }) => {
  // Функция форматирования даты с названием месяца
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

  return (
    <div className="simple-card" style={{ marginBottom: '24px' }}>
      <div style={{ display: 'flex', alignItems: 'center', gap: '16px', marginBottom: '20px' }}>
        <div style={{ 
          width: '64px', 
          height: '64px', 
          background: 'var(--gradient-yellow)', 
          borderRadius: '16px', 
          display: 'flex', 
          alignItems: 'center', 
          justifyContent: 'center',
          color: 'var(--text-dark)',
          boxShadow: 'var(--shadow-button)'
        }}>
          <UserIcon className="w-8 h-8" />
        </div>
        
        <div style={{ flex: 1 }}>
          <h2 className="elderly-title" style={{ fontSize: '20px', marginBottom: '4px', color: 'var(--text-dark)' }}>
            {user.first_name} {user.last_name || ''}
          </h2>
          {user.username && (
            <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-dark-secondary)' }}>
              @{user.username}
            </p>
          )}
        </div>
      </div>

      {subscription ? (
        <div style={{ marginTop: '20px' }}>
                 {/* Статус подписки */}
                 <div className="glass-card" style={{ 
                   padding: '16px',
                   marginBottom: '16px'
                 }}>
                   <p className="elderly-text-large" style={{ fontSize: '16px', margin: '0', color: 'var(--text-dark-strong)' }}>
                     Статус подписки: {subscription.status === 'ACTIVE' ? 'Активен' : 
                                    subscription.status === 'BLOCKED' ? 'Заблокирован' : 
                                    'Истек'}
                   </p>
                 </div>

                 {/* Дата действия */}
                 <div className="glass-card" style={{ 
                   padding: '16px'
                 }}>
                   <span className="elderly-text-large" style={{ fontSize: '16px', margin: '0', color: 'var(--text-dark-strong)' }}>
                     Действует до: {formatDate(subscription.end_date)}
                   </span>
                 </div>
        </div>
      ) : (
        <div style={{ textAlign: 'center', padding: '20px 0' }}>
          <p className="elderly-text-large" style={{ marginBottom: '8px', color: 'var(--text-primary)' }}>
            Статус подписки: ❎ У вас нет активного VPN-ключа
          </p>
          <p className="elderly-text" style={{ fontSize: '14px', color: 'var(--text-secondary)' }}>
            Чтобы получить VPN ключ нажмите на кнопку "Подключиться к VPN"
          </p>
        </div>
      )}
    </div>
  );
};

export default UserProfile;