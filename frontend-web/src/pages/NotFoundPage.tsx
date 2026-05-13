import { Link } from 'react-router-dom';

export default function NotFoundPage() {
  return (
    <section className="not-found">
      <div className="not-found-code">404</div>
      <h1 className="section-title">Страница не найдена</h1>
      <p className="section-lead" style={{ margin: '0 auto 1.25rem' }}>
        Такой страницы нет или ссылка устарела.
      </p>
      <Link to="/" className="btn btn-primary">
        На главную
      </Link>
    </section>
  );
}
