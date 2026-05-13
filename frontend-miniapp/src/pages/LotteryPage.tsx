import React, { useState, useEffect } from 'react';
import { Gift, Ticket, Calendar, Users, ArrowLeft, Edit3, X, RefreshCw, CreditCard } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import api, { miniappApi } from '../services/api';

interface LotteryInfo {
  title: string;
  description: string;
  prize: string;
  prize_image?: string;
  draw_date: string;
  rules: string[];
}

interface TicketStats {
  subscription_payment_tickets: number;
  referral_bonus_tickets: number;
}

interface LotteryTicket {
  id: number;
  ticket_number: string;
  formatted_ticket_number: string;
  source_type: string;
  source_label: string;
  source_description: string;
  created_at: string;
}

interface LotteryData {
  lottery_info: LotteryInfo;
  ticket_stats: TicketStats;
}

const LotteryPage: React.FC = () => {
  const { user, subscription, webApp, isTelegramMode, paymentEnabled } = useTelegram();
  const [lotteryData, setLotteryData] = useState<LotteryData | null>(null);
  const [tickets, setTickets] = useState<LotteryTicket[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<'info' | 'tickets'>('info');
  
  // Состояния для смены номера билета
  const [selectedTicket, setSelectedTicket] = useState<LotteryTicket | null>(null);
  const [newTicketNumber, setNewTicketNumber] = useState('');
  const [isChangingNumber, setIsChangingNumber] = useState(false);
  const [showNumberChangeModal, setShowNumberChangeModal] = useState(false);
  const [showPaymentConfirmation, setShowPaymentConfirmation] = useState(false);
  const [numberAvailabilityError, setNumberAvailabilityError] = useState<string | null>(null);
  const [isCheckingAvailability, setIsCheckingAvailability] = useState(false);

  // Добавляем CSS стили для анимации
  useEffect(() => {
    const style = document.createElement('style');
    style.textContent = `
      @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
      }
      @keyframes ticketGlow {
        0%, 100% { 
          box-shadow: 0 0 5px rgba(220, 38, 38, 0.3), 0 0 10px rgba(220, 38, 38, 0.2);
          transform: scale(1);
        }
        50% { 
          box-shadow: 0 0 15px rgba(220, 38, 38, 0.5), 0 0 25px rgba(220, 38, 38, 0.3);
          transform: scale(1.02);
        }
      }
      @keyframes numberPulse {
        0%, 100% { 
          color: #dc2626;
          text-shadow: 0 0 0px rgba(220, 38, 38, 0);
        }
        50% { 
          color: #b91c1c;
          text-shadow: 0 0 8px rgba(220, 38, 38, 0.4);
        }
      }
      .ticket-card {
        animation: ticketGlow 3s ease-in-out infinite;
        transition: all 0.3s ease;
        position: relative;
        background: linear-gradient(135deg, #fefefe 0%, #f5f5f0 100%) !important;
        border: 2px solid #d4af37 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
      }
      .ticket-card::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: repeating-linear-gradient(
          to bottom,
          transparent 0px,
          transparent 8px,
          #d4af37 8px,
          #d4af37 12px
        );
      }
      .ticket-card::after {
        content: '';
        position: absolute;
        right: 30px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: repeating-linear-gradient(
          to bottom,
          transparent 0px,
          transparent 8px,
          #d4af37 8px,
          #d4af37 12px
        );
      }
      .ticket-card:hover {
        animation-play-state: paused;
        transform: scale(1.05) !important;
        box-shadow: 0 0 20px rgba(220, 38, 38, 0.6) !important;
      }
      .ticket-number {
        animation: numberPulse 2s ease-in-out infinite;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        letter-spacing: 3px;
        color: #dc2626;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .ticket-stripes {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 100%;
        background: repeating-linear-gradient(
          45deg,
          transparent,
          transparent 2px,
          rgba(212, 175, 55, 0.08) 2px,
          rgba(212, 175, 55, 0.08) 4px
        );
        pointer-events: none;
      }
    `;
    document.head.appendChild(style);
    
    return () => {
      document.head.removeChild(style);
    };
  }, []);

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.MainButton.hide();
    }
    
    loadLotteryData();
  }, [webApp, isTelegramMode]);

  const loadLotteryData = async () => {
    try {
      setIsLoading(true);
      
      const token = localStorage.getItem('subscription_token');
      
      if (!token) {
        setError('Токен подписки не найден. Пожалуйста, авторизуйтесь заново.');
        return;
      }

      const [infoResponse, ticketsResponse] = await Promise.all([
        api.get('/miniapp/lottery/info'),
        api.get('/miniapp/lottery/tickets')
      ]);

      if (infoResponse.data.success) {
        setLotteryData(infoResponse.data.data);
      }

      if (ticketsResponse.data.success) {
        setTickets(ticketsResponse.data.data.tickets);
      }

    } catch (err: any) {
      console.error('Error loading lottery data:', err);
      setError('Ошибка загрузки данных лотереи');
    } finally {
      setIsLoading(false);
    }
  };

  // Функции для смены номера билета

  const checkNumberAvailability = async (number: string) => {
    try {
      const response = await api.post('/miniapp/lottery/check-number', { number });
      
      return response.data.success && response.data.data.available;
    } catch (err) {
      console.error('Error checking number availability:', err);
      return false;
    }
  };

  const handleChangeNumber = async (ticket: LotteryTicket) => {
    setSelectedTicket(ticket);
    setNewTicketNumber('');
    setNumberAvailabilityError(null);
    setError(null);
    setShowNumberChangeModal(true);
  };

  // Проверка доступности номера в реальном времени
  const handleNumberInputChange = async (value: string) => {
    setNewTicketNumber(value);
    setNumberAvailabilityError(null);
    
    if (value && value.length >= 1 && value.length <= 4) {
      const num = parseInt(value);
      if (num >= 1 && num <= 9999) {
        setIsCheckingAvailability(true);
        try {
          const isAvailable = await checkNumberAvailability(value);
          if (!isAvailable) {
            setNumberAvailabilityError('Номер уже занят');
          }
        } catch (err) {
          console.error('Error checking availability:', err);
        } finally {
          setIsCheckingAvailability(false);
        }
      }
    }
  };

  const handleConfirmNumberChange = async () => {
    if (!selectedTicket || !newTicketNumber) return;

    // Проверяем, есть ли ошибка доступности
    if (numberAvailabilityError) {
      return;
    }

    try {
      setError(null);

      // Дополнительная проверка доступности номера
      const isAvailable = await checkNumberAvailability(newTicketNumber);
      if (!isAvailable) {
        setNumberAvailabilityError('Номер уже занят');
        return;
      }

      // Показываем экран подтверждения оплаты
      setShowNumberChangeModal(false);
      setShowPaymentConfirmation(true);

    } catch (err: any) {
      console.error('Error checking number availability:', err);
      setError('Ошибка проверки номера');
    }
  };

  const handleProceedToPayment = async () => {
    if (!selectedTicket || !newTicketNumber) return;

    try {
      setIsChangingNumber(true);
      setError(null);

      // Создаем платеж
      const response = await api.post('/miniapp/lottery/change-number-payment', 
        { ticket_id: selectedTicket.id, new_number: newTicketNumber }
      );

      if (response.data.success) {
        // Открываем страницу оплаты
        if (webApp && isTelegramMode) {
          webApp.openLink(response.data.data.payment.payment_url);
        } else {
          window.open(response.data.data.payment.payment_url, '_blank');
        }
        
        setShowPaymentConfirmation(false);
        setSelectedTicket(null);
        setNewTicketNumber('');
        setNumberAvailabilityError(null);
      } else {
        setError(response.data.error || 'Ошибка создания платежа');
      }

    } catch (err: any) {
      console.error('Error creating payment:', err);
      setError('Ошибка создания платежа');
    } finally {
      setIsChangingNumber(false);
    }
  };

  const handleBackToNumberSelection = () => {
    setShowPaymentConfirmation(false);
    setShowNumberChangeModal(true);
    setError(null);
  };

  const getSourceIcon = (sourceType: string) => {
    switch (sourceType) {
      case 'subscription_payment':
        return <Calendar className="w-4 h-4" style={{ color: 'var(--primary-blue)' }} />;
      case 'referral_bonus':
        return <Users className="w-4 h-4" style={{ color: 'var(--accent-yellow)' }} />;
      default:
        return <Ticket className="w-4 h-4" style={{ color: 'var(--text-dark-secondary)' }} />;
    }
  };

  const handleExtendSubscription = () => {
    console.log('Extend subscription button clicked');
    
    // Если платежи отключены, переходим к активации подписки
    if (paymentEnabled === false) {
      window.location.hash = 'activate-subscription';
    } else {
      // Если платежи включены, переходим к выбору планов
      window.location.hash = 'plans';
    }
  };

  const handleReferral = () => {
    console.log('Referral button clicked');
    window.location.hash = 'referral';
  };

  const handleBack = () => {
    window.location.hash = '';
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
            onClick={loadLotteryData}
            className="vpn-button vpn-button-primary"
            style={{ maxWidth: '200px', margin: '0 auto' }}
          >
            Попробовать снова
          </button>
        </div>
      </Layout>
    );
  }

  if (!lotteryData) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <p className="elderly-text-large">Данные не найдены</p>
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
            🎁 {lotteryData.lottery_info.title}
          </h1>
        </div>
        
        {/* Description */}
        <div style={{ textAlign: 'center', marginBottom: '24px' }}>
          <p className="elderly-text">{lotteryData.lottery_info.description}</p>
        </div>

        {/* Табы */}
        <div style={{ 
          display: 'flex', 
          marginBottom: '20px',
          background: 'var(--bg-card-glass)',
          borderRadius: '12px',
          padding: '4px'
        }}>
          <button
            onClick={() => setActiveTab('info')}
            style={{
              flex: 1,
              padding: '12px',
              borderRadius: '8px',
              border: 'none',
              background: activeTab === 'info' ? 'var(--gradient-primary)' : 'transparent',
              color: activeTab === 'info' ? 'white' : 'var(--text-dark)',
              fontWeight: '600',
              cursor: 'pointer'
            }}
          >
            Информация
          </button>
          <button
            onClick={() => setActiveTab('tickets')}
            style={{
              flex: 1,
              padding: '12px',
              borderRadius: '8px',
              border: 'none',
              background: activeTab === 'tickets' ? 'var(--gradient-primary)' : 'transparent',
              color: activeTab === 'tickets' ? 'white' : 'var(--text-dark)',
              fontWeight: '600',
              cursor: 'pointer'
            }}
          >
            Мои билеты ({(() => {
              const totalTickets = lotteryData.ticket_stats.subscription_payment_tickets + lotteryData.ticket_stats.referral_bonus_tickets;
              return totalTickets === 1 ? '1 билет' : totalTickets >= 2 && totalTickets <= 4 ? `${totalTickets} билета` : `${totalTickets} билетов`;
            })()})
          </button>
        </div>

        {activeTab === 'info' && (
          <>
            {/* Информация о призе */}
            <div className="simple-card" style={{ 
              marginBottom: '20px',
              background: 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)',
              border: '1px solid #e2e8f0'
            }}>
              <h2 style={{ 
                display: 'flex', 
                alignItems: 'center', 
                marginBottom: '20px',
                fontSize: '20px',
                fontWeight: '600',
                color: '#1e293b'
              }}>
                <Gift className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
                Приз
              </h2>
              <div style={{ textAlign: 'center' }}>
                {lotteryData.lottery_info.prize_image && (
                  <div style={{ 
                    marginBottom: '20px',
                    position: 'relative',
                    display: 'inline-block'
                  }}>
                    <div style={{
                      background: 'linear-gradient(135deg, #ffffff 0%, #f8fafc 100%)',
                      borderRadius: '20px',
                      padding: '20px',
                      boxShadow: '0 8px 32px rgba(0, 0, 0, 0.1)',
                      border: '1px solid rgba(255, 255, 255, 0.2)',
                      position: 'relative',
                      overflow: 'hidden'
                    }}>
                      <div style={{
                        position: 'absolute',
                        top: '-50%',
                        left: '-50%',
                        width: '200%',
                        height: '200%',
                        background: 'linear-gradient(45deg, transparent 30%, rgba(59, 130, 246, 0.1) 50%, transparent 70%)',
                        animation: 'shimmer 3s infinite',
                        pointerEvents: 'none'
                      }} />
                      <img 
                        src={lotteryData.lottery_info.prize_image} 
                        alt={lotteryData.lottery_info.prize}
                        style={{
                          maxWidth: '180px',
                          maxHeight: '180px',
                          objectFit: 'contain',
                          borderRadius: '12px',
                          position: 'relative',
                          zIndex: 1,
                          filter: 'drop-shadow(0 4px 12px rgba(0, 0, 0, 0.15))'
                        }}
                      />
                    </div>
                  </div>
                )}
                <div style={{
                  background: 'linear-gradient(135deg, var(--primary-blue) 0%, #1e40af 100%)',
                  borderRadius: '16px',
                  padding: '16px 24px',
                  marginBottom: '12px',
                  boxShadow: '0 4px 16px rgba(59, 130, 246, 0.3)'
                }}>
                  <p style={{ 
                    fontSize: '22px', 
                    fontWeight: '700', 
                    color: '#ffffff',
                    margin: '0 0 4px 0',
                    textShadow: '0 1px 2px rgba(0, 0, 0, 0.1)'
                  }}>
                    {lotteryData.lottery_info.prize}
                  </p>
                </div>
              </div>
            </div>

            {/* Статистика билетов */}
            <div className="simple-card" style={{ marginBottom: '20px' }}>
              <h2 style={{ 
                display: 'flex', 
                alignItems: 'center', 
                marginBottom: '16px',
                fontSize: '20px',
                fontWeight: '600',
                color: '#1e293b'
              }}>
                <Ticket className="w-5 h-5 mr-2" style={{ color: 'var(--primary-blue)' }} />
                Ваши билеты
              </h2>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                <div style={{ textAlign: 'center' }}>
                  <p style={{ 
                    fontSize: '18px', 
                    fontWeight: '600', 
                    color: 'var(--accent-yellow)',
                    margin: '0 0 4px 0'
                  }}>
                    {lotteryData.ticket_stats.subscription_payment_tickets}
                  </p>
                  <p style={{ 
                    fontSize: '14px', 
                    color: '#475569',
                    margin: 0
                  }}>
                    За подписку
                  </p>
                </div>
                <div style={{ textAlign: 'center' }}>
                  <p style={{ 
                    fontSize: '18px', 
                    fontWeight: '600', 
                    color: 'var(--primary-blue)',
                    margin: '0 0 4px 0'
                  }}>
                    {lotteryData.ticket_stats.referral_bonus_tickets}
                  </p>
                  <p style={{ 
                    fontSize: '14px', 
                    color: '#475569',
                    margin: 0
                  }}>
                    За рефералов
                  </p>
                </div>
              </div>
            </div>

            {/* Как получить билеты */}
            <div className="simple-card" style={{ marginBottom: '20px' }}>
              <h2 style={{ 
                marginBottom: '20px',
                fontSize: '20px',
                fontWeight: '600',
                color: '#1e293b'
              }}>
                Как получить билеты
              </h2>
              
              {/* Вариант 1: Покупка подписки */}
              <div style={{ marginBottom: '24px' }}>
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  marginBottom: '12px' 
                }}>
                  <CreditCard className="w-5 h-5 mr-2" style={{ color: 'var(--primary-blue)' }} />
                  <h3 style={{ 
                    fontSize: '16px',
                    fontWeight: '600',
                    color: '#1e293b',
                    margin: 0
                  }}>
                    Покупка подписки
                  </h3>
                </div>
                <p style={{ 
                  color: '#475569', 
                  margin: '0 0 16px 0',
                  fontSize: '14px',
                  lineHeight: '1.5'
                }}>
                  За каждую оплаченную подписку вы получаете лотерейные билеты. 
                  Количество билетов равно количеству месяцев подписки.
                </p>
                <button
                  onClick={handleExtendSubscription}
                  className="vpn-button vpn-button-secondary"
                  style={{ width: '100%' }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', justifyContent: 'center' }}>
                    <RefreshCw className="w-4 h-4" />
                    <span>{paymentEnabled === false ? 'Активировать подписку' : 'Продлить подписку'}</span>
                  </div>
                </button>
              </div>

              {/* Разделитель */}
              <div style={{ 
                height: '1px', 
                background: '#e2e8f0', 
                margin: '20px 0' 
              }}></div>

              {/* Вариант 2: Приглашение друга */}
              <div>
                <div style={{ 
                  display: 'flex', 
                  alignItems: 'center', 
                  marginBottom: '12px' 
                }}>
                  <Users className="w-5 h-5 mr-2" style={{ color: 'var(--accent-yellow)' }} />
                  <h3 style={{ 
                    fontSize: '16px',
                    fontWeight: '600',
                    color: '#1e293b',
                    margin: 0
                  }}>
                    Приглашение друга
                  </h3>
                </div>
                <p style={{ 
                  color: '#475569', 
                  margin: '0 0 16px 0',
                  fontSize: '14px',
                  lineHeight: '1.5'
                }}>
                  За каждого приглашенного друга, который оплатил подписку, 
                  вы получаете дополнительный билет.
                </p>
                <button
                  onClick={handleReferral}
                  className="vpn-button vpn-button-secondary"
                  style={{ width: '100%' }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', gap: '8px', justifyContent: 'center' }}>
                    <Users className="w-4 h-4" />
                    <span>Пригласить друга</span>
                  </div>
                </button>
              </div>
            </div>
          </>
        )}

        {activeTab === 'tickets' && (
          <div className="simple-card" style={{ marginBottom: '20px' }}>
            <h2 style={{ 
              marginBottom: '16px',
              fontSize: '20px',
              fontWeight: '600',
              color: '#1e293b',
              display: 'flex',
              alignItems: 'center',
              gap: '8px'
            }}>
              <Gift className="w-5 h-5" style={{ color: 'var(--accent-yellow)' }} />
              Мои билеты для розыгрыша
            </h2>
            {tickets.length === 0 ? (
              <div style={{ textAlign: 'center', padding: '20px' }}>
                <Ticket className="w-12 h-12 mx-auto mb-4" style={{ color: '#cbd5e1' }} />
                <p style={{ color: '#475569', margin: 0 }}>
                  У вас пока нет лотерейных билетов
                </p>
              </div>
            ) : (
              <div style={{
                display: 'grid',
                gridTemplateColumns: 'repeat(2, 1fr)',
                gap: '12px'
              }}>
                {tickets.map((ticket) => (
                  <div key={ticket.id} className="ticket-card" style={{ 
                    display: 'flex', 
                    alignItems: 'center', 
                    justifyContent: 'center',
                    padding: '16px 12px',
                    position: 'relative',
                    overflow: 'hidden',
                    textAlign: 'center',
                    minHeight: '100px'
                  }}>
                    {/* Декоративные полоски */}
                    <div className="ticket-stripes"></div>
                    
                    {/* Основной контент билета */}
                    <div style={{ zIndex: 1, width: '100%' }}>
                      <div style={{
                        background: 'rgba(220, 38, 38, 0.1)',
                        padding: '8px 12px',
                        borderRadius: '8px',
                        border: '2px solid rgba(220, 38, 38, 0.3)',
                        marginBottom: '6px'
                      }}>
                        <p className="ticket-number" style={{ 
                          margin: 0,
                          fontSize: '18px',
                          fontWeight: '700'
                        }}>
                          {ticket.formatted_ticket_number}
                        </p>
                      </div>
                      <p style={{ 
                        color: '#64748b', 
                        margin: 0,
                        fontSize: '10px',
                        fontWeight: '500'
                      }}>
                        Билет для розыгрыша
                      </p>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}

        {/* Модальное окно для смены номера билета */}
        {showNumberChangeModal && selectedTicket && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: 'rgba(0, 0, 0, 0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 1000,
            padding: '20px'
          }}>
            <div style={{
              background: 'white',
              borderRadius: '12px',
              padding: '24px',
              maxWidth: '400px',
              width: '100%',
              maxHeight: '80vh',
              overflow: 'auto'
            }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600', color: '#1e293b' }}>
                  Изменить номер билета
                </h3>
                <button
                  onClick={() => setShowNumberChangeModal(false)}
                  style={{
                    background: 'none',
                    border: 'none',
                    cursor: 'pointer',
                    padding: '4px',
                    borderRadius: '4px'
                  }}
                >
                  <X size={20} color="#64748b" />
                </button>
              </div>

              <div style={{ marginBottom: '16px' }}>
                <p style={{ margin: '0 0 8px 0', color: '#475569', fontSize: '14px' }}>
                  Текущий номер: <strong>{selectedTicket.formatted_ticket_number}</strong>
                </p>
                <p style={{ margin: '0 0 16px 0', color: '#64748b', fontSize: '12px' }}>
                  Стоимость смены номера: <strong>50 ₽</strong>
                </p>
              </div>

              <div style={{ marginBottom: '16px' }}>
                <label style={{ display: 'block', marginBottom: '8px', fontSize: '14px', fontWeight: '500', color: '#374151' }}>
                  Новый номер (1-9999):
                </label>
                <div style={{ position: 'relative' }}>
                  <input
                    type="text"
                    value={newTicketNumber}
                    onChange={(e) => handleNumberInputChange(e.target.value)}
                    placeholder="Введите номер"
                    maxLength={4}
                    style={{
                      width: '100%',
                      padding: '12px',
                      border: numberAvailabilityError ? '2px solid #dc2626' : '2px solid #d1d5db',
                      borderRadius: '8px',
                      fontSize: '18px',
                      fontWeight: '600',
                      color: '#1e293b',
                      outline: 'none',
                      textAlign: 'center',
                      backgroundColor: '#f8fafc'
                    }}
                  />
                  {isCheckingAvailability && (
                    <div style={{
                      position: 'absolute',
                      right: '12px',
                      top: '50%',
                      transform: 'translateY(-50%)',
                      fontSize: '12px',
                      color: '#64748b'
                    }}>
                      Проверка...
                    </div>
                  )}
                </div>
                {numberAvailabilityError && (
                  <div style={{
                    marginTop: '8px',
                    padding: '8px 12px',
                    background: '#fef2f2',
                    border: '1px solid #fecaca',
                    borderRadius: '6px',
                    color: '#dc2626',
                    fontSize: '14px',
                    fontWeight: '500'
                  }}>
                    {numberAvailabilityError}
                  </div>
                )}
              </div>


              {error && (
                <div style={{
                  background: '#fef2f2',
                  border: '1px solid #fecaca',
                  borderRadius: '6px',
                  padding: '12px',
                  marginBottom: '16px'
                }}>
                  <p style={{ margin: 0, color: '#dc2626', fontSize: '14px' }}>
                    {error}
                  </p>
                </div>
              )}

              <div style={{ display: 'flex', gap: '12px' }}>
                <button
                  onClick={() => setShowNumberChangeModal(false)}
                  style={{
                    flex: 1,
                    padding: '12px',
                    border: '1px solid #d1d5db',
                    borderRadius: '8px',
                    background: 'white',
                    color: '#374151',
                    cursor: 'pointer',
                    fontWeight: '500'
                  }}
                >
                  Отмена
                </button>
                        <button
                          onClick={handleConfirmNumberChange}
                          disabled={!newTicketNumber || isChangingNumber || numberAvailabilityError || isCheckingAvailability}
                          style={{
                            flex: 1,
                            padding: '12px',
                            border: 'none',
                            borderRadius: '8px',
                            background: (!newTicketNumber || isChangingNumber || numberAvailabilityError || isCheckingAvailability) ? '#9ca3af' : 'var(--primary-blue)',
                            color: 'white',
                            cursor: (!newTicketNumber || isChangingNumber || numberAvailabilityError || isCheckingAvailability) ? 'not-allowed' : 'pointer',
                            fontWeight: '500'
                          }}
                        >
                          {isChangingNumber ? 'Обработка...' : isCheckingAvailability ? 'Проверка...' : 'Продолжить'}
                        </button>
              </div>
            </div>
          </div>
        )}

                {/* Модальное окно подтверждения оплаты */}
                {showPaymentConfirmation && selectedTicket && (
                  <div style={{
                    position: 'fixed',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    background: 'rgba(0, 0, 0, 0.5)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    zIndex: 1000,
                    padding: '20px'
                  }}>
                    <div style={{
                      background: 'white',
                      borderRadius: '12px',
                      padding: '24px',
                      maxWidth: '400px',
                      width: '100%',
                      maxHeight: '80vh',
                      overflow: 'auto'
                    }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '20px' }}>
                        <h3 style={{ margin: 0, fontSize: '18px', fontWeight: '600', color: '#1e293b' }}>
                          Подтверждение оплаты
                        </h3>
                        <button
                          onClick={handleBackToNumberSelection}
                          style={{
                            background: 'none',
                            border: 'none',
                            cursor: 'pointer',
                            padding: '4px',
                            borderRadius: '4px'
                          }}
                        >
                          <X size={20} color="#64748b" />
                        </button>
                      </div>

                      <div style={{ marginBottom: '20px' }}>
                        <div style={{ 
                          background: '#f8fafc', 
                          borderRadius: '8px', 
                          padding: '16px', 
                          marginBottom: '16px',
                          border: '1px solid #e2e8f0'
                        }}>
                          <h4 style={{ 
                            margin: '0 0 8px 0', 
                            fontSize: '16px', 
                            fontWeight: '600', 
                            color: '#1e293b' 
                          }}>
                            Смена номера лотерейного билета
                          </h4>
                          <p style={{ 
                            margin: '0 0 8px 0', 
                            fontSize: '14px', 
                            color: '#475569' 
                          }}>
                            Билет №{selectedTicket.formatted_ticket_number}
                          </p>
                          <p style={{ 
                            margin: '0', 
                            fontSize: '14px', 
                            color: '#475569' 
                          }}>
                            Новый номер: <strong>{newTicketNumber.padStart(4, '0')}</strong>
                          </p>
                        </div>

                        <div style={{ 
                          display: 'flex', 
                          justifyContent: 'space-between', 
                          alignItems: 'center',
                          padding: '12px 16px',
                          background: 'linear-gradient(45deg, var(--primary-blue), #4a90e2)',
                          borderRadius: '8px',
                          color: 'white'
                        }}>
                          <span style={{ fontSize: '16px', fontWeight: '500' }}>
                            Стоимость услуги:
                          </span>
                          <span style={{ fontSize: '20px', fontWeight: '700' }}>
                            50 ₽
                          </span>
                        </div>
                      </div>

                      {error && (
                        <div style={{
                          background: '#fef2f2',
                          border: '1px solid #fecaca',
                          borderRadius: '6px',
                          padding: '12px',
                          marginBottom: '16px'
                        }}>
                          <p style={{ margin: 0, color: '#dc2626', fontSize: '14px' }}>
                            {error}
                          </p>
                        </div>
                      )}

                      <div style={{ display: 'flex', gap: '12px' }}>
                        <button
                          onClick={handleBackToNumberSelection}
                          style={{
                            flex: 1,
                            padding: '12px',
                            border: '1px solid #d1d5db',
                            borderRadius: '8px',
                            background: 'white',
                            color: '#374151',
                            cursor: 'pointer',
                            fontWeight: '500'
                          }}
                        >
                          Вернуться
                        </button>
                        <button
                          onClick={handleProceedToPayment}
                          disabled={isChangingNumber}
                          style={{
                            flex: 1,
                            padding: '12px',
                            border: 'none',
                            borderRadius: '8px',
                            background: isChangingNumber ? '#9ca3af' : 'var(--primary-blue)',
                            color: 'white',
                            cursor: isChangingNumber ? 'not-allowed' : 'pointer',
                            fontWeight: '500'
                          }}
                        >
                          {isChangingNumber ? 'Обработка...' : 'Оплатить 50 ₽'}
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {/* Кнопка результативной таблицы */}
        <button
          onClick={() => window.location.hash = 'lottery-leaderboard'}
          className="vpn-button vpn-button-primary"
          style={{ marginBottom: '16px' }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <Ticket className="w-4 h-4" />
            Смотреть Результативную таблицу розыгрыша
          </div>
        </button>

        {/* Кнопка назад */}
        <button
          onClick={() => window.location.hash = ''}
          className="vpn-button vpn-button-secondary"
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
            <ArrowLeft className="w-4 h-4" />
            На главную
          </div>
        </button>
      </div>
    </Layout>
  );
};

export default LotteryPage;
