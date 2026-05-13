import { useEffect, useState } from 'react';
import { miniappApi, API_BASE_URL } from '../services/api';
import api from '../services/api';

// Declare Telegram WebApp global
declare global {
  interface Window {
    Telegram?: {
      WebApp: {
        initData: string;
        initDataUnsafe: {
          user?: {
            id: number;
            first_name: string;
            last_name?: string;
            username?: string;
            language_code?: string;
            is_premium?: boolean;
          };
          chat_instance?: string;
          chat_type?: string;
          auth_date?: number;
          hash?: string;
        };
        version: string;
        platform: string;
        colorScheme: 'light' | 'dark';
        themeParams: {
          bg_color?: string;
          text_color?: string;
          hint_color?: string;
          link_color?: string;
          button_color?: string;
          button_text_color?: string;
          secondary_bg_color?: string;
        };
        isExpanded: boolean;
        viewportHeight: number;
        viewportStableHeight: number;
        headerColor: string;
        backgroundColor: string;
        isClosingConfirmationEnabled: boolean;
        BackButton: {
          isVisible: boolean;
          onClick: (callback: () => void) => void;
          offClick: (callback: () => void) => void;
          show: () => void;
          hide: () => void;
        };
        MainButton: {
          text: string;
          color: string;
          textColor: string;
          isVisible: boolean;
          isProgressVisible: boolean;
          isActive: boolean;
          setText: (text: string) => void;
          onClick: (callback: () => void) => void;
          offClick: (callback: () => void) => void;
          show: () => void;
          hide: () => void;
          enable: () => void;
          disable: () => void;
          showProgress: (leaveActive?: boolean) => void;
          hideProgress: () => void;
          setParams: (params: {
            text?: string;
            color?: string;
            text_color?: string;
            is_active?: boolean;
            is_visible?: boolean;
          }) => void;
        };
        ready: () => void;
        expand: () => void;
        close: () => void;
        sendData: (data: string) => void;
        switchInlineQuery: (query: string, choose_chat_types?: string[]) => void;
        openLink: (url: string, options?: { try_instant_view?: boolean }) => void;
        openTelegramLink: (url: string) => void;
        openInvoice: (url: string, callback?: (status: string) => void) => void;
        showPopup: (params: {
          title?: string;
          message: string;
          buttons?: Array<{
            id?: string;
            type?: 'default' | 'ok' | 'close' | 'cancel' | 'destructive';
            text?: string;
          }>;
        }, callback?: (buttonId: string) => void) => void;
        showAlert: (message: string, callback?: () => void) => void;
        showConfirm: (message: string, callback?: (confirmed: boolean) => void) => void;
        showScanQrPopup: (params: {
          text?: string;
        }, callback?: (text: string) => void) => void;
        closeScanQrPopup: () => void;
        readTextFromClipboard: (callback?: (text: string) => void) => void;
        requestWriteAccess: (callback?: (granted: boolean) => void) => void;
        requestContact: (callback?: (granted: boolean) => void) => void;
      };
    };
  }
}

export const useTelegram = () => {
  const [user, setUser] = useState<{
    id: number;
    first_name: string;
    last_name?: string;
    username?: string;
    language_code?: string;
    is_premium?: boolean;
    is_channel_subscribed?: boolean;
  } | null>(null);
  const [subscription, setSubscription] = useState<any>(null);
  const [vpnKeyUrl, setVpnKeyUrl] = useState<string | null>(null);
  const [supportChannel, setSupportChannel] = useState<string>('@your_support_channel');
  const [lotteryEnabled, setLotteryEnabled] = useState<boolean>(false);
  const [channelName, setChannelName] = useState<string>('');
  const [channelLink, setChannelLink] = useState<string>('');
  const [checkChannelSubscription, setCheckChannelSubscription] = useState<boolean>(true);
  const [isLoading, setIsLoading] = useState(true);
  const [webApp, setWebApp] = useState<typeof window.Telegram.WebApp | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [isTelegramMode, setIsTelegramMode] = useState(false);

  useEffect(() => {
    const initializeApp = async () => {
      try {
        const isDev = import.meta.env.MODE === 'development';

        if (isDev) {
          console.log('=== TELEGRAM WEBAPP DEBUG ===');
          console.log('Window Telegram:', window.Telegram);
          console.log('Window Telegram WebApp:', window.Telegram?.WebApp);
        }

        // Проверяем кеш авторизации в sessionStorage (только для текущей сессии)
        const cachedAuthData = sessionStorage.getItem('telegram_auth_data');
        if (cachedAuthData) {
          try {
            const authData = JSON.parse(cachedAuthData);
            if (isDev) console.log('Using cached auth data');

            setUser(authData.user);
            setSubscription(authData.subscription);
            setVpnKeyUrl(authData.vpn_key_url || null);
            setLotteryEnabled(authData.lottery_enabled || false);
            setSupportChannel(authData.support_channel || '@your_support_channel');
            setChannelName(authData.channel_name || '');
            setChannelLink(authData.channel_link || '');
            setCheckChannelSubscription(authData.check_channel_subscription !== undefined ? authData.check_channel_subscription : true);

            if (authData.subscription?.token) {
              localStorage.setItem('subscription_token', authData.subscription.token);
            }

            if (window.Telegram?.WebApp) {
              const tg = window.Telegram.WebApp;
              tg.ready();
              tg.expand();
              setWebApp(tg);
              setIsTelegramMode(true);

              document.body.style.backgroundColor = tg.backgroundColor;
              document.body.style.color = tg.themeParams.text_color || '#000000';
            }

            setIsLoading(false);
            return;
          } catch (e) {
            if (isDev) console.error('Failed to parse cached auth data:', e);
            sessionStorage.removeItem('telegram_auth_data');
          }
        }
        
        // Проверяем, что Telegram WebApp доступен
        if (!window.Telegram?.WebApp) {
          if (isDev) console.log('Telegram WebApp not available - running in browser mode');
          setIsTelegramMode(false);
          
          // Создаем mock пользователя для браузера
          const mockUser = {
            id: 12345,
            first_name: 'Browser',
            last_name: 'User',
            username: 'browser_user',
            language_code: 'en',
            is_premium: false
          };
          
          setUser(mockUser);
          setIsLoading(false);
          return;
        }

        const tg = window.Telegram.WebApp;
        setIsTelegramMode(true);
        
        // Инициализация Telegram WebApp
        tg.ready();
        tg.expand();

        // API запрос с правильной обработкой
        if (isDev) {
          console.log('=== API AUTHENTICATION ===');
          console.log('initData:', tg.initData);
        }
        
        try {
          const response = await api.post('/miniapp/auth', {}, {
            headers: {
              'X-Telegram-Init-Data': tg.initData || 'test_data'
            }
          });
          
          if (isDev) console.log('Response status:', response.status);
          
          const data = response.data;
          if (isDev) console.log('Response data:', data);
          
          if (data.success) {
            if (isDev) console.log('API success, setting data...');

            setUser(data.user);
            setSubscription(data.subscription);
            setVpnKeyUrl(data.vpn_key_url || null);
            setLotteryEnabled(data.lottery_enabled || false);

            // Сохраняем токен подписки в localStorage для использования на других страницах
            if (data.subscription?.token) {
              localStorage.setItem('subscription_token', data.subscription.token);
              if (isDev) console.log('Subscription token saved to localStorage');
            }

            if (data.support_channel) {
              setSupportChannel(data.support_channel);
            }

            if (data.channel_name) {
              setChannelName(data.channel_name);
            }

            if (data.channel_link) {
              setChannelLink(data.channel_link);
            }

            if (data.check_channel_subscription !== undefined) {
              setCheckChannelSubscription(data.check_channel_subscription);
            }

            // Кешируем данные авторизации в sessionStorage
            sessionStorage.setItem('telegram_auth_data', JSON.stringify({
              user: data.user,
              subscription: data.subscription,
              vpn_key_url: data.vpn_key_url,
              lottery_enabled: data.lottery_enabled,
              support_channel: data.support_channel,
              channel_name: data.channel_name,
              channel_link: data.channel_link,
              check_channel_subscription: data.check_channel_subscription,
            }));

            if (isDev) console.log('Auth data cached for session');
          } else {
            if (isDev) console.log('API returned success: false');
            throw new Error('API returned success: false');
          }
        } catch (apiError) {
          if (isDev) console.error('API request failed:', apiError);
          
          // Fallback на Telegram данные
          const userData = tg.initDataUnsafe.user;
          if (userData) {
            if (isDev) console.log('Using Telegram user data as fallback');
            setUser(userData);
          } else {
            if (isDev) console.log('No user data available, using mock data');
            const mockUser = {
              id: 67890,
              first_name: 'Telegram',
              last_name: 'User',
              username: 'telegram_user',
              language_code: 'en',
              is_premium: false
            };
            setUser(mockUser);
          }
        }

        setWebApp(tg);
        setIsLoading(false);

        // Настройка темы
        document.body.style.backgroundColor = tg.backgroundColor;
        document.body.style.color = tg.themeParams.text_color || '#000000';

      } catch (err) {
        const isDev = import.meta.env.MODE === 'development';
        if (isDev) console.error('Error initializing Telegram WebApp:', err);
        setError('Failed to initialize Telegram WebApp');
        setIsLoading(false);
      }
    };

    initializeApp();
  }, []);

  // Функция для обновления состояния пользователя
  const updateUser = (updatedUser: typeof user) => {
    setUser(updatedUser);
    // Обновляем кеш в sessionStorage
    const cachedAuthData = sessionStorage.getItem('telegram_auth_data');
    if (cachedAuthData) {
      try {
        const authData = JSON.parse(cachedAuthData);
        authData.user = updatedUser;
        sessionStorage.setItem('telegram_auth_data', JSON.stringify(authData));
      } catch (e) {
        console.error('Error updating cached auth data:', e);
      }
    }
  };

  return {
    user,
    subscription,
    vpnKeyUrl,
    supportChannel,
    lotteryEnabled,
    channelName,
    channelLink,
    checkChannelSubscription,
    updateUser,
    webApp,
    isLoading,
    error,
    isReady: !isLoading && !error,
    isTelegramMode
  };
};
