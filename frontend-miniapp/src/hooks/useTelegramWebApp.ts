import { useEffect, useState } from 'react';
import type { User } from '../types';

export const useTelegramWebApp = () => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const tg = window.Telegram?.WebApp;
    
    if (tg) {
      tg.ready();
      tg.expand();
      
      // Enable closing confirmation
      tg.isClosingConfirmationEnabled = true;
      
      // Set user data
      if (tg.initDataUnsafe?.user) {
        setUser(tg.initDataUnsafe.user);
      }
      
      setIsLoading(false);
    } else {
      // For development without Telegram
      console.warn('Telegram WebApp not available');
      setUser({
        id: 12345,
        first_name: 'Test',
        last_name: 'User',
        username: 'testuser'
      });
      setIsLoading(false);
    }
  }, []);

  const showMainButton = (text: string, onClick: () => void) => {
    const tg = window.Telegram?.WebApp;
    if (tg?.MainButton) {
      tg.MainButton.setText(text);
      tg.MainButton.show();
      tg.MainButton.onClick(onClick);
    }
  };

  const hideMainButton = () => {
    const tg = window.Telegram?.WebApp;
    if (tg?.MainButton) {
      tg.MainButton.hide();
    }
  };

  const showBackButton = (onClick: () => void) => {
    const tg = window.Telegram?.WebApp;
    if (tg?.BackButton) {
      tg.BackButton.show();
      tg.BackButton.onClick(onClick);
    }
  };

  const hideBackButton = () => {
    const tg = window.Telegram?.WebApp;
    if (tg?.BackButton) {
      tg.BackButton.hide();
    }
  };

  const hapticFeedback = {
    impact: (style: 'light' | 'medium' | 'heavy' = 'medium') => {
      window.Telegram?.WebApp?.HapticFeedback?.impactOccurred(style);
    },
    notification: (type: 'error' | 'success' | 'warning') => {
      window.Telegram?.WebApp?.HapticFeedback?.notificationOccurred(type);
    },
    selection: () => {
      window.Telegram?.WebApp?.HapticFeedback?.selectionChanged();
    }
  };

  const close = () => {
    window.Telegram?.WebApp?.close();
  };

  return {
    user,
    isLoading,
    showMainButton,
    hideMainButton,
    showBackButton,
    hideBackButton,
    hapticFeedback,
    close,
    tg: window.Telegram?.WebApp
  };
};