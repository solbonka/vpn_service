import { Link } from 'react-router-dom';
import Logo from '../components/Logo';

export default function HomePage() {
  return (
    <section className="home-page">
      <div className="hero">
        <div className="hero-visual">
          <Logo size="xl" showLabel={false} />
        </div>
        <div>
          <h1 className="section-title">VPN Бичурское</h1>
          <p className="section-lead">
            Быстрое подключение из Telegram Mini App и с этого сайта. Один сервис — удобный доступ с телефона и
            компьютера.
          </p>
          <div className="hero-actions">
            <Link to="/pricing" className="btn btn-primary">
              Тарифы
            </Link>
            <Link to="/login" className="btn btn-secondary">
              Войти
            </Link>
          </div>
        </div>
      </div>

      <div className="cards" style={{ marginTop: '2rem' }}>
        <article className="card">
          <h3>Mini App</h3>
          <p>Управление подпиской и ключами прямо в Telegram.</p>
        </article>
        <article className="card">
          <h3>Сайт</h3>
          <p>Вход по почте или через Telegram — профиль и статус подписки в браузере.</p>
        </article>
        <article className="card card--accent">
          <h3>Поддержка</h3>
          <p>Если что-то не работает — напишите в поддержку через бота.</p>
        </article>
      </div>
    </section>
  );
}
