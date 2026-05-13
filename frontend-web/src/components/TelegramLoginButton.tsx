import { useEffect, useMemo } from 'react';
import type { TelegramWidgetUser } from '../lib/api';

declare global {
  interface Window {
    onTelegramAuth?: (user: TelegramWidgetUser) => void;
  }
}

type Props = {
  onAuth: (user: TelegramWidgetUser) => void;
};

export default function TelegramLoginButton({ onAuth }: Props) {
  const botUsername = useMemo(
    () => import.meta.env.VITE_TELEGRAM_BOT_USERNAME || 'your_bot_username',
    [],
  );

  useEffect(() => {
    window.onTelegramAuth = onAuth;

    const wrapper = document.getElementById('telegram-login-widget');
    if (!wrapper) {
      return;
    }

    wrapper.innerHTML = '';
    const script = document.createElement('script');
    script.async = true;
    script.src = 'https://telegram.org/js/telegram-widget.js?22';
    script.setAttribute('data-telegram-login', botUsername);
    script.setAttribute('data-size', 'large');
    script.setAttribute('data-userpic', 'false');
    script.setAttribute('data-request-access', 'write');
    script.setAttribute('data-onauth', 'onTelegramAuth(user)');
    wrapper.appendChild(script);

    return () => {
      if (wrapper) {
        wrapper.innerHTML = '';
      }
      delete window.onTelegramAuth;
    };
  }, [botUsername, onAuth]);

  return <div id="telegram-login-widget" />;
}
