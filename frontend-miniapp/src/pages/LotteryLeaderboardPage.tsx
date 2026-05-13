import React, { useState, useEffect } from 'react';
import { ArrowLeft, Trophy, Ticket } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { miniappApi } from '../services/api';

interface LeaderboardEntry {
  ticket_number: string;
  formatted_ticket_number: string;
  owner_name: string;
  is_current_user: boolean;
  created_at: string;
}

interface LeaderboardData {
  leaderboard: LeaderboardEntry[];
  total_tickets: number;
}

const LotteryLeaderboardPage: React.FC = () => {
  const { webApp, isTelegramMode } = useTelegram();
  const [leaderboardData, setLeaderboardData] = useState<LeaderboardData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.MainButton.hide();
    }
    
    loadLeaderboardData();
  }, [webApp, isTelegramMode]);

  const loadLeaderboardData = async () => {
    try {
      setIsLoading(true);
      
      const response = await miniappApi.lottery.getLeaderboard();
      
      if (response.data.success) {
        setLeaderboardData(response.data.data);
      } else {
        setError(response.data.error || 'Ошибка загрузки данных');
      }
    } catch (err: any) {
      console.error('Error loading leaderboard data:', err);
      setError('Ошибка загрузки таблицы результатов');
    } finally {
      setIsLoading(false);
    }
  };

  const handleBack = () => {
    window.location.hash = 'lottery';
  };


  if (isLoading) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
          <p className="elderly-text-large">Загрузка таблицы...</p>
        </div>
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <p className="warning-text" style={{ marginBottom: '20px', fontSize: '18px' }}>{error}</p>
          <button
            onClick={loadLeaderboardData}
            className="vpn-button vpn-button-primary"
            style={{ maxWidth: '200px', margin: '0 auto', marginBottom: '16px' }}
          >
            Попробовать снова
          </button>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{ maxWidth: '200px', margin: '0 auto' }}
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Назад
          </button>
        </div>
      </Layout>
    );
  }

  if (!leaderboardData) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <p className="elderly-text-large">Данные не найдены</p>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{ maxWidth: '200px', margin: '0 auto', marginTop: '16px' }}
          >
            <ArrowLeft className="w-4 h-4 mr-2" />
            Назад
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
            🏆 Результативная таблица розыгрыша
          </h1>
        </div>

        {/* Статистика */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px', marginBottom: '16px' }}>
            <Trophy className="w-6 h-6" style={{ color: 'var(--accent-yellow)' }} />
            <h2 style={{ 
              fontSize: '20px',
              fontWeight: '600',
              color: '#1e293b',
              margin: '0'
            }}>
              Общая статистика
            </h2>
          </div>
          <div style={{ 
            display: 'flex', 
            justifyContent: 'center',
            alignItems: 'center',
            gap: '24px'
          }}>
            <div style={{ textAlign: 'center' }}>
              <div style={{ 
                fontSize: '24px', 
                fontWeight: '700', 
                color: 'var(--primary-blue)',
                marginBottom: '4px'
              }}>
                {leaderboardData.total_tickets}
              </div>
              <div style={{ 
                fontSize: '14px', 
                color: '#64748b',
                fontWeight: '500'
              }}>
                Всего билетов
              </div>
            </div>
            <div style={{ textAlign: 'center' }}>
              <div style={{ 
                fontSize: '24px', 
                fontWeight: '700', 
                color: 'var(--accent-yellow)',
                marginBottom: '4px'
              }}>
                {new Set(leaderboardData.leaderboard.map(entry => entry.owner_name)).size}
              </div>
              <div style={{ 
                fontSize: '14px', 
                color: '#64748b',
                fontWeight: '500'
              }}>
                Участников
              </div>
            </div>
          </div>
        </div>

        {/* Таблица билетов */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <h2 style={{ 
            display: 'flex', 
            alignItems: 'center', 
            marginBottom: '16px',
            fontSize: '18px',
            fontWeight: '600',
            color: '#1e293b'
          }}>
            <Ticket className="w-5 h-5 mr-2" style={{ color: 'var(--primary-blue)' }} />
            Все билеты ({leaderboardData.leaderboard.length})
          </h2>
          
          {leaderboardData.leaderboard.length === 0 ? (
            <div style={{ 
              textAlign: 'center', 
              padding: '40px 20px',
              color: '#64748b'
            }}>
              <Ticket className="w-12 h-12 mx-auto mb-4" style={{ color: '#d1d5db' }} />
              <p style={{ fontSize: '16px', margin: '0' }}>
                Пока нет билетов в розыгрыше
              </p>
            </div>
          ) : (
            <div style={{ 
              maxHeight: '400px', 
              overflowY: 'auto',
              border: '1px solid #e2e8f0',
              borderRadius: '8px'
            }}>
              <table style={{ 
                width: '100%', 
                borderCollapse: 'collapse',
                fontSize: '14px'
              }}>
                <thead style={{ 
                  background: '#f8fafc',
                  position: 'sticky',
                  top: 0,
                  zIndex: 1
                }}>
                  <tr>
                    <th style={{ 
                      padding: '12px 8px', 
                      textAlign: 'left', 
                      fontWeight: '600',
                      color: '#374151',
                      borderBottom: '1px solid #e2e8f0',
                      fontSize: '12px'
                    }}>
                      № Билета
                    </th>
                    <th style={{ 
                      padding: '12px 8px', 
                      textAlign: 'left', 
                      fontWeight: '600',
                      color: '#374151',
                      borderBottom: '1px solid #e2e8f0',
                      fontSize: '12px'
                    }}>
                      Владелец
                    </th>
                  </tr>
                </thead>
                <tbody>
                  {leaderboardData.leaderboard.map((entry, index) => (
                    <tr key={index} style={{ 
                      borderBottom: index < leaderboardData.leaderboard.length - 1 ? '1px solid #f1f5f9' : 'none',
                      backgroundColor: entry.is_current_user ? '#fef3c7' : 'transparent',
                      borderLeft: entry.is_current_user ? '4px solid #f59e0b' : 'none'
                    }}>
                      <td style={{ 
                        padding: '12px 8px',
                        fontWeight: '600',
                        color: entry.is_current_user ? '#d97706' : 'var(--primary-blue)',
                        fontFamily: 'monospace'
                      }}>
                        {entry.formatted_ticket_number}
                      </td>
                      <td style={{ 
                        padding: '12px 8px',
                        color: entry.is_current_user ? '#92400e' : '#374151',
                        maxWidth: '200px',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                        fontWeight: entry.is_current_user ? '600' : 'normal'
                      }}>
                        {entry.owner_name}
                        {entry.is_current_user && (
                          <span style={{ 
                            marginLeft: '8px',
                            fontSize: '10px',
                            color: '#f59e0b',
                            fontWeight: '600'
                          }}>
                            (Ваш)
                          </span>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Кнопка назад */}
        <button
          onClick={handleBack}
          className="vpn-button vpn-button-secondary"
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <ArrowLeft className="w-4 h-4" />
            Назад к розыгрышу
          </div>
        </button>
      </div>
    </Layout>
  );
};

export default LotteryLeaderboardPage;
