import React, { useState, useEffect } from 'react';
import { Copy, Share2, Gift, Calendar, Ticket, Shield, Link, Globe, Zap, ArrowLeft } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

interface ReferralData {
  referral_code: string;
  referral_link: string;
  referral_count: number;
  balance: number;
  balance_in_rubles: number;
  lottery_tickets: number;
  bonus_amount: number;
  available_bonus_types: Array<{
    value: string;
    label: string;
    description: string;
  }>;
  is_program_active: boolean;
  instructions: string;
  bonus_types: Array<{
    type: string;
    label: string;
    description: string;
  }>;
}

const ReferralPage: React.FC = () => {
  const { subscription, webApp, isTelegramMode } = useTelegram();
  const [referralData, setReferralData] = useState<ReferralData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [copied, setCopied] = useState(false);

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.MainButton.hide();
    }
    
    loadReferralData();
  }, [webApp, isTelegramMode]);

  const loadReferralData = async () => {
    try {
      setIsLoading(true);
      
      // Получаем токен из localStorage (как это делается в useTelegram)
      const token = localStorage.getItem('subscription_token');
      
      console.log('Loading referral data with token:', token);
      console.log('Subscription from hook:', subscription);
      
      if (!token) {
        setError('Токен подписки не найден. Пожалуйста, авторизуйтесь заново.');
        return;
      }

      const response = await api.get('/miniapp/referral/info', {
        headers: {
          'X-Sub-Token': token
        }
      });

      console.log('Referral API response:', response.data);

      if (response.data.success) {
        setReferralData(response.data.data);
      } else {
        setError(response.data.error || 'Ошибка загрузки данных');
      }
    } catch (err: any) {
      console.error('Error loading referral data:', err);
      console.error('Error details:', err.response?.data);
      setError('Ошибка загрузки данных реферальной программы');
    } finally {
      setIsLoading(false);
    }
  };

  const handleBack = () => {
    window.location.hash = '';
  };

  const copyReferralLink = async () => {
    if (!referralData?.referral_link) return;

    try {
      await navigator.clipboard.writeText(referralData.referral_link);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error('Failed to copy:', err);
    }
  };


  const getBonusIcon = (type: string) => {
    switch (type) {
      case 'balance':
      case 'rubles':
        return <Gift className="w-5 h-5" style={{ color: 'var(--accent-yellow)' }} />;
      case 'days':
        return <Calendar className="w-5 h-5" style={{ color: 'var(--primary-blue)' }} />;
      case 'lottery_ticket':
      case 'lottery_tickets':
        return <Ticket className="w-5 h-5" style={{ color: 'var(--light-blue)' }} />;
      default:
        return <Gift className="w-5 h-5" style={{ color: 'var(--text-dark-secondary)' }} />;
    }
  };

  if (isLoading) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
          <p className="elderly-text-large">Загрузка данных...</p>
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
            onClick={loadReferralData}
            className="vpn-button vpn-button-primary"
            style={{ maxWidth: '200px', margin: '0 auto' }}
          >
            Попробовать снова
          </button>
        </div>
      </Layout>
    );
  }

  if (!referralData) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <p className="elderly-text-large">Данные не найдены</p>
        </div>
      </Layout>
    );
  }

  if (!referralData.is_program_active) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <p className="warning-text" style={{ marginBottom: '20px', fontSize: '18px' }}>Реферальная программа временно недоступна</p>
          <button
            onClick={() => window.location.hash = ''}
            className="vpn-button vpn-button-primary"
            style={{ maxWidth: '200px', margin: '0 auto' }}
          >
            На главную
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
            🎁 Реферальная программа
          </h1>
        </div>

        {/* Информация о бонусе */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <div>
            {referralData.bonus_types.map((bonusType, index) => (
              <div key={index}>
                {bonusType.type === 'balance' || bonusType.type === 'rubles' ? (
                  <>
                    <h2 style={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      marginBottom: '16px',
                      fontSize: '20px',
                      fontWeight: '600',
                      color: '#1e293b'
                    }}>
                      <Gift className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
                      Ваш бонус за приглашение
                    </h2>
                    <span className="success-text" style={{ 
                      textAlign: 'center',
                      fontSize: '16px',
                      fontWeight: '600',
                      display: 'block'
                    }}>
                      {referralData.bonus_amount} руб.
                    </span>
                  </>
                ) : bonusType.type === 'days' ? (
                  <>
                    <h2 style={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      marginBottom: '16px',
                      fontSize: '20px',
                      fontWeight: '600',
                      color: '#1e293b'
                    }}>
                      <Gift className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
                      Ваш бонус за приглашение
                    </h2>
                    <span className="success-text" style={{ 
                      textAlign: 'center',
                      fontSize: '16px',
                      fontWeight: '600',
                      display: 'block'
                    }}>
                      {referralData.bonus_amount} дн.
                    </span>
                  </>
                ) : (
                  <>
                    <h2 style={{ 
                      display: 'flex', 
                      alignItems: 'center', 
                      marginBottom: '16px',
                      fontSize: '20px',
                      fontWeight: '600',
                      color: '#1e293b'
                    }}>
                      <Gift className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
                      Приглашай друзей
                    </h2>
                    <div style={{
                      padding: '16px',
                      backgroundColor: '#f8fafc',
                      borderRadius: '8px',
                      borderLeft: '3px solid var(--accent-yellow)'
                    }}>
                      <p style={{
                        fontSize: '15px',
                        lineHeight: '1.6',
                        color: '#475569',
                        margin: 0,
                        textAlign: 'center'
                      }}>
                        Пригласи друзей и за каждого друга получи билет для участия в розыгрыше. 
                        Чем больше билетов тем выше шанс выиграть приз!
                      </p>
                    </div>
                  </>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Как получить бонус */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <h2 style={{ 
            display: 'flex', 
            alignItems: 'center', 
            marginBottom: '16px',
            fontSize: '20px',
            fontWeight: '600',
            color: '#1e293b'
          }}>
            <Gift className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
            Как получить бонус?
          </h2>
          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            <div style={{ 
              display: 'flex', 
              alignItems: 'flex-start', 
              gap: '12px',
              padding: '12px',
              background: '#f8fafc',
              borderRadius: '8px',
              border: '1px solid #e2e8f0'
            }}>
              <div style={{
                width: '24px',
                height: '24px',
                borderRadius: '50%',
                background: '#3b82f6',
                color: '#ffffff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '12px',
                fontWeight: '600',
                flexShrink: 0,
                marginTop: '2px'
              }}>
                1
              </div>
              <div>
                <p style={{ 
                  margin: '0 0 4px 0', 
                  fontSize: '14px', 
                  fontWeight: '600',
                  color: '#1e293b'
                }}>
                  Скопируйте вашу ссылку
                </p>
                <p style={{ 
                  margin: '0', 
                  fontSize: '13px', 
                  color: '#64748b',
                  lineHeight: '1.4'
                }}>
                  Нажмите "Копировать"
                </p>
              </div>
            </div>

            <div style={{ 
              display: 'flex', 
              alignItems: 'flex-start', 
              gap: '12px',
              padding: '12px',
              background: '#f8fafc',
              borderRadius: '8px',
              border: '1px solid #e2e8f0'
            }}>
              <div style={{
                width: '24px',
                height: '24px',
                borderRadius: '50%',
                background: '#3b82f6',
                color: '#ffffff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '12px',
                fontWeight: '600',
                flexShrink: 0,
                marginTop: '2px'
              }}>
                2
              </div>
              <div>
                <p style={{ 
                  margin: '0 0 4px 0', 
                  fontSize: '14px', 
                  fontWeight: '600',
                  color: '#1e293b'
                }}>
                  Поделитесь с другом
                </p>
                <p style={{ 
                  margin: '0', 
                  fontSize: '13px', 
                  color: '#64748b',
                  lineHeight: '1.4'
                }}>
                  Отправьте ссылку другу
                </p>
              </div>
            </div>

            <div style={{ 
              display: 'flex', 
              alignItems: 'flex-start', 
              gap: '12px',
              padding: '12px',
              background: '#f8fafc',
              borderRadius: '8px',
              border: '1px solid #e2e8f0'
            }}>
              <div style={{
                width: '24px',
                height: '24px',
                borderRadius: '50%',
                background: '#3b82f6',
                color: '#ffffff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '12px',
                fontWeight: '600',
                flexShrink: 0,
                marginTop: '2px'
              }}>
                3
              </div>
              <div>
                <p style={{ 
                  margin: '0 0 4px 0', 
                  fontSize: '14px', 
                  fontWeight: '600',
                  color: '#1e293b'
                }}>
                  Друг открывает бота
                </p>
                <p style={{ 
                  margin: '0', 
                  fontSize: '13px', 
                  color: '#64748b',
                  lineHeight: '1.4'
                }}>
                  По вашей ссылке
                </p>
              </div>
            </div>

            <div style={{ 
              display: 'flex', 
              alignItems: 'flex-start', 
              gap: '12px',
              padding: '12px',
              background: '#f8fafc',
              borderRadius: '8px',
              border: '1px solid #e2e8f0'
            }}>
              <div style={{
                width: '24px',
                height: '24px',
                borderRadius: '50%',
                background: '#3b82f6',
                color: '#ffffff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '12px',
                fontWeight: '600',
                flexShrink: 0,
                marginTop: '2px'
              }}>
                4
              </div>
              <div>
                <p style={{ 
                  margin: '0 0 4px 0', 
                  fontSize: '14px', 
                  fontWeight: '600',
                  color: '#1e293b'
                }}>
                  Друг оплачивает подписку
                </p>
                <p style={{ 
                  margin: '0', 
                  fontSize: '13px', 
                  color: '#64748b',
                  lineHeight: '1.4'
                }}>
                  Покупает любой тариф
                </p>
              </div>
            </div>

            <div style={{ 
              display: 'flex', 
              alignItems: 'flex-start', 
              gap: '12px',
              padding: '12px',
              background: '#d1fae5',
              borderRadius: '8px',
              border: '2px solid #a7f3d0'
            }}>
              <div style={{
                width: '24px',
                height: '24px',
                borderRadius: '50%',
                background: '#059669',
                color: '#ffffff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                fontSize: '12px',
                fontWeight: '600',
                flexShrink: 0,
                marginTop: '2px'
              }}>
                ✓
              </div>
              <div>
                <p style={{ 
                  margin: '0 0 4px 0', 
                  fontSize: '14px', 
                  fontWeight: '600',
                  color: '#059669'
                }}>
                  Получаете билет!
                </p>
                <p style={{ 
                  margin: '0', 
                  fontSize: '13px', 
                  color: '#047857',
                  lineHeight: '1.4'
                }}>
                  Сразу после оплаты
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Реферальная ссылка */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <h2 style={{ 
            display: 'flex', 
            alignItems: 'center', 
            marginBottom: '16px',
            fontSize: '20px',
            fontWeight: '600',
            color: '#1e293b'
          }}>
            <Share2 className="w-5 h-5 mr-2" style={{ color: 'var(--primary-blue)' }} />
            Ваша реферальная ссылка
          </h2>
          <div style={{ 
            background: '#f8fafc', 
            borderRadius: '8px', 
            padding: '12px', 
            marginBottom: '16px',
            border: '1px solid #e2e8f0'
          }}>
            <p style={{ 
              fontSize: '14px', 
              color: '#1e293b', 
              wordBreak: 'break-all',
              margin: 0,
              fontWeight: '500'
            }}>
              {referralData.referral_link}
            </p>
          </div>
          <button
            onClick={copyReferralLink}
            className={`vpn-button ${copied ? 'vpn-button-primary' : 'vpn-button-secondary'}`}
            style={{ marginBottom: 0 }}
          >
            <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
              <Copy className="w-4 h-4" />
              {copied ? 'Скопировано!' : 'Копировать'}
            </div>
          </button>
        </div>

        {/* Дополнительная информация о VPN */}
        <div className="simple-card" style={{ marginBottom: '20px' }}>
          <h2 style={{ 
            display: 'flex', 
            alignItems: 'center', 
            marginBottom: '16px',
            fontSize: '18px',
            fontWeight: '600',
            color: '#1e293b',
            lineHeight: '1.4'
          }}>
            <Shield className="w-5 h-5 mr-2" style={{ color: 'var(--primary-blue)', flexShrink: 0 }} />
            Почему вашему другу стоит присоединиться к нам?
          </h2>
          <p style={{ 
            color: '#64748b', 
            fontSize: '14px', 
            margin: '0 0 16px 0',
            lineHeight: '1.5'
          }}>
            У нас подписка стоит <span style={{ color: 'var(--accent-yellow)', fontWeight: '600' }}>ВСЕГО 100 руб/месяц</span> и за это вы получите:
          </p>
          <ul style={{ 
            margin: 0, 
            padding: '0 0 0 20px',
            listStyle: 'none',
            display: 'flex',
            flexDirection: 'column',
            gap: '10px'
          }}>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>📱</span>
              Один ключ можно использовать для нескольких устройств
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>🚫</span>
              Никакой рекламы
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>🚀</span>
              Действительно высокая скорость
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>⏰</span>
              Стабильная работа 24/7
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>🛡️</span>
              Безопасность
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>🌍</span>
              В одной подписке несколько ключей из разных регионов
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>🎁</span>
              Регулярное проведение акций и розыгрышей
            </li>
            <li style={{ 
              color: '#475569', 
              fontSize: '13px', 
              lineHeight: '1.5',
              position: 'relative',
              paddingLeft: '0'
            }}>
              <span style={{ marginRight: '8px' }}>💬</span>
              Моментальная поддержка 24/7 по всем вопросам
            </li>
          </ul>
        </div>

        {/* Кнопка назад */}
        <button
          onClick={() => window.location.hash = ''}
          className="vpn-button vpn-button-secondary"
        >
          На главную
        </button>
      </div>
    </Layout>
  );
};

export default ReferralPage;
