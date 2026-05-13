import { useEffect, useState } from 'react';
import { fetchWebClientMe } from '../lib/api';
import { getAuthSession, saveAuthSession } from '../lib/auth';

export default function ProfilePage() {
  const session = getAuthSession();
  const [user, setUser] = useState(session?.user);
  const subscription = session?.subscription;

  useEffect(() => {
    const current = getAuthSession();
    if (current?.provider !== 'web_email' || !current.accessToken) {
      return;
    }
    let cancelled = false;
    fetchWebClientMe()
      .then(({ user: u }) => {
        if (cancelled) {
          return;
        }
        const nextUser = {
          id: u.id,
          email: u.email,
          first_name: u.name ?? '',
          username: u.email,
        };
        setUser(nextUser);
        saveAuthSession({
          ...current,
          user: nextUser,
        });
      })
      .catch(() => {});
    return () => {
      cancelled = true;
    };
  }, []);

  const providerLabel =
    session?.provider === 'telegram' ? 'Telegram' : session?.provider === 'web_email' ? 'Email (аккаунт сайта)' : '—';

  return (
    <section>
      <div className="page-header">
        <h1 className="section-title">Профиль</h1>
        <p className="section-lead">
          Способ входа: <strong style={{ color: 'var(--color-text)' }}>{providerLabel}</strong>
        </p>
      </div>

      <div className="profile-grid">
        {user && (
          <div className="card">
            <h3>Пользователь</h3>
            <p>
              <span className="stat-label">ID</span>
              <div className="stat-value">{user.id}</div>
            </p>
            {user.email && (
              <p style={{ marginTop: '0.85rem' }}>
                <span className="stat-label">Email</span>
                <div className="stat-value">{user.email}</div>
              </p>
            )}
            <p style={{ marginTop: '0.85rem' }}>
              <span className="stat-label">Имя</span>
              <div className="stat-value">
                {[user.first_name, user.last_name].filter(Boolean).join(' ') || '—'}
              </div>
            </p>
            {session?.provider === 'telegram' && (
              <p style={{ marginTop: '0.85rem' }}>
                <span className="stat-label">Username</span>
                <div className="stat-value">{user.username || '—'}</div>
              </p>
            )}
          </div>
        )}

        <div className="card">
          <h3>Подписка VPN</h3>
          {session?.provider === 'web_email' && !subscription && (
            <p>
              У аккаунта сайта пока нет привязанной подписки. Выберите тариф на странице "Тарифы", затем
              свяжитесь с поддержкой для активации оплаты в web-кабинете.
            </p>
          )}
          {!subscription ? (
            session?.provider === 'telegram' ? (
              <p>Нет активной подписки или она не подтянулась.</p>
            ) : null
          ) : (
            <>
              <p>
                <span className="stat-label">Статус</span>
                <div className="stat-value">{subscription.status}</div>
              </p>
              <p style={{ marginTop: '0.85rem' }}>
                <span className="stat-label">Тариф</span>
                <div className="stat-value">{subscription.plan || '—'}</div>
              </p>
              <p style={{ marginTop: '0.85rem' }}>
                <span className="stat-label">Действует до</span>
                <div className="stat-value">{subscription.end_date || '—'}</div>
              </p>
            </>
          )}
        </div>
      </div>
    </section>
  );
}
