import { useCallback, useState } from 'react';
import type { FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import TelegramLoginButton from '../components/TelegramLoginButton';
import Logo from '../components/Logo';
import { loginWebClient, loginByTelegramWidget, registerWebClient, type TelegramWidgetUser } from '../lib/api';
import { saveAuthSession } from '../lib/auth';

type AuthMode = 'login' | 'register';

function formatApiErrors(err: unknown): string | null {
  if (typeof err === 'object' && err !== null && 'response' in err) {
    const res = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
    const data = res?.data;
    if (data?.errors) {
      const parts = Object.entries(data.errors).flatMap(([k, v]) => v.map((m) => `${k}: ${m}`));
      return parts.join(' ');
    }
    if (typeof data?.message === 'string') {
      return data.message;
    }
  }
  return null;
}

export default function LoginPage() {
  const navigate = useNavigate();
  const [mode, setMode] = useState<AuthMode>('login');
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const persistWebSession = (token: string, user: { id: number; email: string; name: string | null }) => {
    saveAuthSession({
      provider: 'web_email',
      accessToken: token,
      user: {
        id: user.id,
        email: user.email,
        first_name: user.name ?? '',
        username: user.email,
      },
      subscription: null,
    });
  };

  const onWebSubmit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    setIsLoading(true);
    setError(null);

    try {
      if (mode === 'register') {
        const data = await registerWebClient({
          email,
          password,
          password_confirmation: passwordConfirmation,
          name: name.trim() || undefined,
        });
        persistWebSession(data.token, data.user);
      } else {
        const data = await loginWebClient(email, password);
        persistWebSession(data.token, data.user);
      }
      navigate('/profile');
    } catch (err) {
      setError(formatApiErrors(err) ?? (mode === 'register' ? 'Не удалось зарегистрироваться.' : 'Неверный email или пароль.'));
    } finally {
      setIsLoading(false);
    }
  };

  const onTelegramLogin = useCallback(
    async (telegramUser: TelegramWidgetUser) => {
      setIsLoading(true);
      setError(null);
      try {
        const data = await loginByTelegramWidget(telegramUser);
        saveAuthSession({
          provider: 'telegram',
          user: data.user,
          subscription: data.subscription,
        });
        navigate('/profile');
      } catch {
        setError('Не удалось войти через Telegram. Сначала откройте бота и нажмите Start.');
      } finally {
        setIsLoading(false);
      }
    },
    [navigate],
  );

  return (
    <section className="page-narrow">
      <div className="page-header page-header--center">
        <div className="logo-hero">
          <Logo size="lg" showLabel={false} />
        </div>
        <h1 className="section-title">Вход и регистрация</h1>
        <p className="section-lead" style={{ margin: '0 auto' }}>
          Создайте аккаунт на сайте или войдите существующим email. Для клиентов из Telegram остаётся вход через виджет.
        </p>
      </div>

      <aside className="login-help">
        <h2 className="login-help-title">Как это устроено</h2>
        <ul className="login-help-list">
          <li>
            <strong>Аккаунт на сайте</strong> — вкладки «Вход» и «Регистрация»: отдельная база пользователей сайта (не
            админ-панель). Подписка VPN по-прежнему привязана к боту; позже можно связать аккаунт с Telegram.
          </li>
          <li>
            <strong>Telegram</strong> — если вы уже пользовались ботом, можно войти через кнопку ниже без пароля на
            сайте.
          </li>
          <li>
            <strong>Администраторы</strong> — вход в админку по-прежнему через отдельное приложение{' '}
            <code>frontend</code> и <code>POST /api/admin/auth/login</code>.
          </li>
        </ul>
      </aside>

      <div className="login-layout">
        <form className="card" onSubmit={onWebSubmit}>
          <div className="auth-tabs" role="tablist">
            <button
              type="button"
              role="tab"
              aria-selected={mode === 'login'}
              className={`auth-tab ${mode === 'login' ? 'auth-tab--active' : ''}`}
              onClick={() => {
                setMode('login');
                setError(null);
              }}
            >
              Вход
            </button>
            <button
              type="button"
              role="tab"
              aria-selected={mode === 'register'}
              className={`auth-tab ${mode === 'register' ? 'auth-tab--active' : ''}`}
              onClick={() => {
                setMode('register');
                setError(null);
              }}
            >
              Регистрация
            </button>
          </div>

          {mode === 'register' && (
            <label>
              Имя (необязательно)
              <input
                value={name}
                onChange={(event) => setName(event.target.value)}
                type="text"
                autoComplete="name"
                placeholder="Как к вам обращаться"
              />
            </label>
          )}
          <label>
            Email
            <input value={email} onChange={(event) => setEmail(event.target.value)} type="email" required autoComplete="email" />
          </label>
          <label>
            Пароль
            <input
              value={password}
              onChange={(event) => setPassword(event.target.value)}
              type="password"
              required
              autoComplete={mode === 'register' ? 'new-password' : 'current-password'}
              minLength={8}
            />
          </label>
          {mode === 'register' && (
            <label>
              Пароль ещё раз
              <input
                value={passwordConfirmation}
                onChange={(event) => setPasswordConfirmation(event.target.value)}
                type="password"
                required
                autoComplete="new-password"
                minLength={8}
              />
            </label>
          )}
          <button type="submit" className="btn btn-primary btn-submit" disabled={isLoading}>
            {isLoading ? 'Подождите…' : mode === 'register' ? 'Зарегистрироваться' : 'Войти'}
          </button>
        </form>

        <div className="card">
          <h3>Telegram</h3>
          <p style={{ fontSize: '0.92rem' }}>Виджет входа — если вы уже открывали бота.</p>
          <div className="telegram-widget-wrap">
            <TelegramLoginButton onAuth={onTelegramLogin} />
          </div>
        </div>
      </div>

      {error && <p className="error-text">{error}</p>}
    </section>
  );
}
