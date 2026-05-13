import React, { useEffect, useState } from 'react';
import { useTelegram } from './hooks/useTelegram';
import HomePage from './pages/HomePage';
import DeviceSelectionPage from './pages/DeviceSelectionPage';
import AppInstallPage from './pages/AppInstallPage';
import SubscriptionSetupPage from './pages/SubscriptionSetupPage';
import ManualAddPage from './pages/ManualAddPage';
import VpnSuccessPage from './pages/VpnSuccessPage';
import OrderConfirmationPage from './pages/OrderConfirmationPage';
import ConnectPage from './pages/ConnectPage';
import ConnectClientPage from './pages/ConnectClientPage';
import ConnectInstructionsPage from './pages/ConnectInstructionsPage';
import SubscriptionsPage from './pages/SubscriptionsPage';
import HelpPage from './pages/HelpPage';
import PlansPage from './pages/PlansPage';
import DurationsPage from './pages/DurationsPage';
import PaymentPage from './pages/PaymentPage';
import ActivateSubscriptionPage from './pages/ActivateSubscriptionPage';
import ReferralPage from './pages/ReferralPage';
import LotteryPage from './pages/LotteryPage';
import LotteryLeaderboardPage from './pages/LotteryLeaderboardPage';
import ProfilePage from './pages/ProfilePage';
import TermsPage from './pages/TermsPage';
import SharedPaymentPage from './pages/SharedPaymentPage';

const App: React.FC = () => {
  // КРИТИЧЕСКИ ВАЖНО: Проверяем публичный роут ДО вызова useTelegram()!
  // useTelegram() делает API запросы, которые не нужны для публичных страниц
  const currentHash = window.location.hash; // #/pay/token
  
  // Если это публичный роут - рендерим без авторизации
  // Проверяем с # в начале: #/pay/ или просто /pay/
  if (currentHash.includes('/pay/')) {
    return <SharedPaymentPage />;
  }

  // Для всех остальных роутов - используем обычную логику с авторизацией
  return <AppWithAuth />;
};

// Компонент с авторизацией (для приватных роутов)
const AppWithAuth: React.FC = () => {
  const { user, subscription, webApp, isLoading, error, isReady, isTelegramMode } = useTelegram();
  const [currentPage, setCurrentPage] = useState(window.location.hash.slice(1) || '');
  
  console.log('App render - isLoading:', isLoading, 'error:', error, 'user:', user, 'isReady:', isReady, 'currentPage:', currentPage);

  useEffect(() => {
    // Set theme colors when webApp is ready
    if (webApp && webApp.themeParams) {
      const root = document.documentElement;
      Object.entries(webApp.themeParams).forEach(([key, value]) => {
        if (value) {
          root.style.setProperty(`--tg-theme-${key.replace(/_/g, '-')}`, value);
        }
      });
    }
  }, [webApp]);

  // Listen for hash changes
  useEffect(() => {
    const handleHashChange = () => {
      const newPage = window.location.hash.slice(1) || '';
      console.log('Hash changed to:', newPage);
      setCurrentPage(newPage);
    };

    // Listen for hash changes
    window.addEventListener('hashchange', handleHashChange);
    
    // Also listen for popstate events (back/forward buttons)
    window.addEventListener('popstate', handleHashChange);

    return () => {
      window.removeEventListener('hashchange', handleHashChange);
      window.removeEventListener('popstate', handleHashChange);
    };
  }, []);

  // Show loading state
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
          <p className="text-white text-lg">Loading user data...</p>
        </div>
      </div>
    );
  }

  // Show error state
  if (error) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-center">
          <div className="text-red-500 text-6xl mb-4">⚠️</div>
          <h1 className="text-white text-xl mb-2">Error</h1>
          <p className="text-gray-300">{error}</p>
          <p className="text-gray-400 text-sm mt-4">
            Make sure you're opening this app from Telegram
          </p>
        </div>
      </div>
    );
  }

  // If no user data, show message
  if (!user) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-center">
          <p className="text-white text-lg">No user data available</p>
          <p className="text-gray-400 text-sm mt-2">Please try again</p>
        </div>
      </div>
    );
  }

  const renderPage = () => {
    console.log('Rendering page:', currentPage);
    
    // Handle nested routes
    if (currentPage.startsWith('plans/')) {
      if (currentPage.includes('/durations/') && currentPage.includes('/payment')) {
        return <PaymentPage />;
      } else if (currentPage.includes('/durations')) {
        return <DurationsPage />;
      } else {
        return <PlansPage />;
      }
    }
    
    // Handle connect routes
    if (currentPage.startsWith('connect/')) {
      const parts = currentPage.split('/');
      if (parts.length === 3) {
        return <ConnectInstructionsPage />;
      } else if (parts.length === 2) {
        // Новый флоу: connect/{os} ведет к установке приложения
        return <AppInstallPage />;
      }
    }
    
    // Handle new VPN setup routes
    if (currentPage.startsWith('vpn-setup/')) {
      return <SubscriptionSetupPage />;
    }
    
    if (currentPage.startsWith('manual-add/')) {
      return <ManualAddPage />;
    }
    
    if (currentPage === 'vpn-status') {
      // TODO: Создать страницу проверки статуса подключения
      return <ConnectClientPage />;
    }
    
    if (currentPage === 'vpn-success') {
      return <VpnSuccessPage />;
    }
    
    if (currentPage.startsWith('order-confirmation')) {
      return <OrderConfirmationPage />;
    }
    
    switch (currentPage) {
      case 'connect':
        return <DeviceSelectionPage />;
      case 'connect-old':
        return <ConnectPage />;
      case 'plans':
        return <PlansPage />;
      case 'activate-subscription':
        return <ActivateSubscriptionPage />;
      case 'subscriptions':
        return <SubscriptionsPage />;
      case 'help':
        return <HelpPage />;
      case 'referral':
        return <ReferralPage />;
      case 'lottery':
        return <LotteryPage />;
      case 'lottery-leaderboard':
        return <LotteryLeaderboardPage />;
      case 'profile':
        return <ProfilePage />;
      case 'terms':
        return <TermsPage />;
      default:
        return <HomePage />;
    }
  };

  return renderPage();
};

export default App;