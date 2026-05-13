import React, { useState, useEffect } from 'react';
import { Play, HelpCircle, RefreshCw, Users, Gift, User } from 'lucide-react';
import Layout from '../components/Layout';
import VpnStatusCard from '../components/VpnStatusCard';
import { useTelegram } from '../hooks/useTelegram';
import api from '../services/api';

const HomePage: React.FC = () => {
  const { user, subscription, vpnKeyUrl, webApp, isTelegramMode, lotteryEnabled, channelName, channelLink, checkChannelSubscription, updateUser } = useTelegram();
  const [paymentEnabled, setPaymentEnabled] = useState<boolean | null>(null);
  const [showActiveSubscriptionModal, setShowActiveSubscriptionModal] = useState(false);
  const [showChannelSubscriptionModal, setShowChannelSubscriptionModal] = useState(false);
  const [isCheckingSubscription, setIsCheckingSubscription] = useState(false);

  useEffect(() => {
    // Скрываем MainButton на главной странице
    if (webApp && isTelegramMode) {
      webApp.MainButton.hide();
    }
    
    // Загружаем информацию о платежах
    loadPaymentInfo();
  }, [webApp, isTelegramMode]);

  const handleConnect = async () => {
    console.log('Connect button clicked');
    
    // Проверяем подписку на канал только если включена проверка
    if (checkChannelSubscription) {
      // Если подписка уже подтверждена, сразу переходим к подключению
      if (user && user.is_channel_subscribed === true) {
        console.log('User already subscribed to channel');
        window.location.hash = 'connect';
        return;
      }
      
      // Проверяем подписку на канал перед подключением
      if (user && user.is_channel_subscribed === false) {
        console.log('User not subscribed to channel');
        setShowChannelSubscriptionModal(true);
        return;
      }
      
      // Если статус подписки неизвестен, проверяем через API
      if (subscription?.token && (user?.is_channel_subscribed === undefined || user?.is_channel_subscribed === null)) {
        try {
          const response = await api.get('/miniapp/subscription/');
          const responseData = response.data;
          
          if (responseData.success) {
            if (responseData.user?.is_channel_subscribed === true) {
              // Обновляем состояние пользователя
              updateUser({ ...user, is_channel_subscribed: true });
              // Переходим к подключению
              window.location.hash = 'connect';
              return;
            } else if (responseData.user?.is_channel_subscribed === false) {
              // Обновляем состояние пользователя
              updateUser({ ...user, is_channel_subscribed: false });
              console.log('User not subscribed to channel (from API)');
              setShowChannelSubscriptionModal(true);
              return;
            }
          }
        } catch (error) {
          console.error('Ошибка при проверке подписки:', error);
          // В случае ошибки все равно разрешаем подключение
        }
      }
    }
    
    // Если подписка подтверждена или проверка не требуется, переходим к подключению
    window.location.hash = 'connect';
  };

  const handleExtendSubscription = () => {
    console.log('Extend subscription button clicked');
    
    // Если платежи отключены
    if (paymentEnabled === false) {
      // Проверяем статус подписки - если не BLOCKED, то показываем сообщение
      if (subscription?.status !== 'BLOCKED') {
        // Если подписка еще активна, показываем модальное окно
        setShowActiveSubscriptionModal(true);
        return;
      } else {
        // Если подписка заблокирована, переходим к активации
        window.location.hash = 'activate-subscription';
      }
    } else {
      // Если платежи включены, переходим к выбору планов
      window.location.hash = 'plans';
    }
  };

  const loadPaymentInfo = async () => {
    try {
      const response = await api.get('/miniapp/subscription/plans');
      
      const data = response.data;
      setPaymentEnabled(data.payment_enabled);
    } catch (err) {
      console.error('Error loading payment info:', err);
    }
  };

  const handleHelp = () => {
    console.log('Help button clicked');
    window.location.hash = 'help';
  };

  const handleProfile = () => {
    console.log('Profile button clicked');
    window.location.hash = 'profile';
  };

  const handleReferral = () => {
    console.log('Referral button clicked');
    window.location.hash = 'referral';
  };

  const handleLottery = () => {
    console.log('Lottery button clicked');
    window.location.hash = 'lottery';
  };



  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  // Отладочная информация
  console.log('Subscription data:', subscription);
  console.log('Plan name:', subscription?.plan);

  return (
    <Layout>
      <div className="elderly-friendly" style={{ padding: '20px' }}>
        {/* Название приложения */}
        <div style={{
          textAlign: 'center',
          marginBottom: '20px'
        }}>
          <h1 style={{
            fontSize: '24px',
            fontWeight: '700',
            color: 'var(--text-primary)',
            margin: 0,
            textShadow: '0 2px 4px rgba(0, 0, 0, 0.1)'
          }}>
            {(import.meta as any).env.VITE_APP_NAME || __APP_NAME__ || 'VPN Aginskoe'}
          </h1>
        </div>

        {/* Центральный VPN статус */}
        <VpnStatusCard 
          status={subscription?.status || null}
          subscriptionEndDate={subscription?.end_date}
          planName={subscription?.plan}
          isConnected={false}
          vpnKeyUrl={vpnKeyUrl}
        />

        {/* Основная кнопка подключения */}
        <button
          onClick={handleConnect}
          className="vpn-button vpn-button-primary"
          style={{ marginBottom: '16px' }}
        >
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
            <Play className="w-6 h-6" />
            <span>Подключиться к VPN</span>
          </div>
        </button>

        {/* Кнопки в зависимости от состояния лотереи */}
        {lotteryEnabled ? (
          <>
            {/* Когда лотерея включена: Продлить + Пригласить друга */}
            <div className="button-row">
              <button
                onClick={handleExtendSubscription}
                className="vpn-button vpn-button-secondary"
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <RefreshCw className="w-5 h-5" />
                  <span>Продлить</span>
                </div>
              </button>

              <button
                onClick={handleReferral}
                className="vpn-button vpn-button-secondary"
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <Users className="w-5 h-5" />
                  <span>Пригласить друга</span>
                </div>
              </button>
            </div>

            {/* Лотерея на всю ширину */}
            <button
              onClick={handleLottery}
              className="vpn-button vpn-button-primary"
              style={{ marginTop: '8px' }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <Gift className="w-5 h-5" />
                <span>Участвовать в розыгрыше Iphone!</span>
              </div>
            </button>

            {/* Профиль на всю ширину */}
            <button
              onClick={handleProfile}
              className="vpn-button vpn-button-secondary"
              style={{ marginTop: '8px' }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <User className="w-5 h-5" />
                <span>Профиль</span>
              </div>
            </button>

            {/* Поддержка на всю ширину */}
            <button
              onClick={handleHelp}
              className="vpn-button vpn-button-secondary"
              style={{ marginTop: '8px' }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <HelpCircle className="w-5 h-5" />
                <span>Поддержка</span>
              </div>
            </button>
          </>
        ) : (
          <>
            {/* Когда лотерея выключена: Продлить + Поддержка */}
            <div className="button-row">
              <button
                onClick={handleExtendSubscription}
                className="vpn-button vpn-button-secondary"
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <RefreshCw className="w-5 h-5" />
                  <span>Продлить</span>
                </div>
              </button>

              <button
                onClick={handleHelp}
                className="vpn-button vpn-button-secondary"
              >
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                  <HelpCircle className="w-5 h-5" />
                  <span>Поддержка</span>
                </div>
              </button>
            </div>

            {/* Профиль на всю ширину */}
            <button
              onClick={handleProfile}
              className="vpn-button vpn-button-secondary"
              style={{ marginTop: '8px' }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                <User className="w-5 h-5" />
                <span>Профиль</span>
              </div>
            </button>
          </>
        )}

        {/* Модальное окно для активной подписки */}
        {showActiveSubscriptionModal && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: 'var(--gradient-primary)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 1000,
            padding: '20px'
          }}>
            <div style={{ 
              maxWidth: '400px', 
              width: '100%',
              textAlign: 'center',
              position: 'relative',
              padding: '40px 20px',
              marginBottom: '30px',
              background: 'var(--bg-card-glass)',
              borderRadius: '16px',
              border: '1px solid var(--border-light)',
              boxShadow: 'var(--shadow-card)'
            }}>
              <button
                onClick={() => setShowActiveSubscriptionModal(false)}
                style={{
                  position: 'absolute',
                  top: '16px',
                  right: '16px',
                  background: 'none',
                  border: 'none',
                  fontSize: '24px',
                  color: 'var(--text-dark-secondary)',
                  cursor: 'pointer',
                  width: '32px',
                  height: '32px',
                  borderRadius: '50%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
              >
                ×
              </button>
              
              <h2 style={{ 
                fontSize: '24px',
                fontWeight: '700',
                color: 'var(--accent-yellow)',
                marginBottom: '12px',
                marginTop: '16px',
                lineHeight: '1.4'
              }}>
                Подписка активна
              </h2>
              <p style={{ 
                fontSize: '18px',
                lineHeight: '1.7',
                color: 'var(--text-dark-secondary)',
                marginBottom: '24px'
              }}>
                Ваша подписка еще АКТИВНА, продление будет доступно после истечения срока подписки!
              </p>
              
              <div style={{ display: 'flex', justifyContent: 'center' }}>
                <button
                  onClick={() => setShowActiveSubscriptionModal(false)}
                  className="elderly-button"
                  style={{ maxWidth: '200px' }}
                >
                  Понятно
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Модальное окно для подписки на канал */}
        {showChannelSubscriptionModal && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            background: 'var(--gradient-primary)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 1000,
            padding: '20px'
          }}>
            <div style={{ 
              maxWidth: '400px', 
              width: '100%',
              textAlign: 'center',
              position: 'relative',
              padding: '40px 20px',
              marginBottom: '30px',
              background: 'var(--bg-card-glass)',
              borderRadius: '16px',
              border: '1px solid var(--border-light)',
              boxShadow: 'var(--shadow-card)'
            }}>
              <button
                onClick={() => setShowChannelSubscriptionModal(false)}
                style={{
                  position: 'absolute',
                  top: '16px',
                  right: '16px',
                  background: 'none',
                  border: 'none',
                  fontSize: '24px',
                  color: 'var(--text-dark-secondary)',
                  cursor: 'pointer',
                  width: '32px',
                  height: '32px',
                  borderRadius: '50%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center'
                }}
              >
                ×
              </button>
              
              <div style={{ fontSize: '48px', marginBottom: '20px' }}>📢</div>
              
              <h2 style={{ 
                fontSize: '24px',
                fontWeight: '700',
                color: 'var(--accent-yellow)',
                marginBottom: '12px',
                marginTop: '0',
                lineHeight: '1.4'
              }}>
                Подписка на канал
              </h2>
              <p style={{ 
                fontSize: '18px',
                lineHeight: '1.7',
                color: 'var(--text-dark-secondary)',
                marginBottom: '24px'
              }}>
                Для подключения VPN необходимо подписаться на наш канал {channelName}
              </p>
              
              <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                <button
                  onClick={() => {
                    // Нормализуем ссылку на канал
                    let normalizedLink = channelLink;
                    
                    // Если ссылка начинается с @, убираем @ и добавляем https://t.me/
                    if (normalizedLink.startsWith('@')) {
                      normalizedLink = `https://t.me/${normalizedLink.slice(1)}`;
                    }
                    // Если ссылка начинается с t.me/, добавляем https://
                    else if (normalizedLink.startsWith('t.me/')) {
                      normalizedLink = `https://${normalizedLink}`;
                    }
                    // Если ссылка не начинается с http, добавляем https://
                    else if (!normalizedLink.startsWith('http')) {
                      normalizedLink = `https://t.me/${normalizedLink.replace(/^@/, '')}`;
                    }
                    
                    if (webApp && isTelegramMode) {
                      // Открываем канал в Telegram
                      webApp.openTelegramLink(normalizedLink);
                    } else {
                      // Для браузера открываем в новой вкладке
                      window.open(normalizedLink, '_blank');
                    }
                  }}
                  className="vpn-button vpn-button-primary"
                >
                  Подписаться на канал
                </button>
                
                <button
                  onClick={async () => {
                    if (isCheckingSubscription) return; // Предотвращаем повторные клики
                    
                    setIsCheckingSubscription(true);
                    try {
                      // Проверяем подписку на канал через API
                      const response = await api.get('/miniapp/subscription/');
                      const responseData = response.data;
                      
                      if (responseData.success && responseData.user?.is_channel_subscribed === true) {
                        // Подписка подтверждена, обновляем состояние пользователя
                        if (user) {
                          updateUser({ ...user, is_channel_subscribed: true });
                        }
                        // Закрываем модалку
                        setShowChannelSubscriptionModal(false);
                        // Продолжаем с подключением
                        window.location.hash = 'connect';
                      } else {
                        // Подписка не подтверждена, показываем сообщение
                        if (webApp && isTelegramMode) {
                          webApp.showAlert('Пожалуйста, подпишитесь на канал и попробуйте снова');
                        } else {
                          alert('Пожалуйста, подпишитесь на канал и попробуйте снова');
                        }
                      }
                    } catch (error) {
                      console.error('Ошибка при проверке подписки:', error);
                      // В случае ошибки показываем сообщение
                      if (webApp && isTelegramMode) {
                        webApp.showAlert('Не удалось проверить подписку. Попробуйте позже');
                      } else {
                        alert('Не удалось проверить подписку. Попробуйте позже');
                      }
                    } finally {
                      setIsCheckingSubscription(false);
                    }
                  }}
                  className="vpn-button vpn-button-secondary"
                  disabled={isCheckingSubscription}
                  style={{
                    opacity: isCheckingSubscription ? 0.6 : 1,
                    cursor: isCheckingSubscription ? 'not-allowed' : 'pointer',
                    position: 'relative'
                  }}
                >
                  {isCheckingSubscription ? (
                    <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: '8px' }}>
                      <span style={{
                        width: '16px',
                        height: '16px',
                        border: '2px solid var(--text-dark-secondary)',
                        borderTop: '2px solid transparent',
                        borderRadius: '50%',
                        animation: 'spin 1s linear infinite',
                        display: 'inline-block'
                      }}></span>
                      Проверка...
                    </span>
                  ) : (
                    'Проверить подписку'
                  )}
                </button>
                
                <button
                  onClick={() => setShowChannelSubscriptionModal(false)}
                  style={{
                    background: 'none',
                    border: 'none',
                    color: 'var(--text-dark-secondary)',
                    fontSize: '16px',
                    padding: '8px',
                    cursor: 'pointer'
                  }}
                >
                  Отмена
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default HomePage;