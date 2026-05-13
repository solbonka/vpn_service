import type { ReactElement } from 'react';
import { Link, Navigate, Route, Routes, useNavigate } from 'react-router-dom';
import Logo from './components/Logo';
import './App.css';
import { getAuthSession } from './lib/auth';
import { logoutSessionCompletely } from './lib/api';
import HomePage from './pages/HomePage';
import LoginPage from './pages/LoginPage';
import NotFoundPage from './pages/NotFoundPage';
import PricingPage from './pages/PricingPage';
import ProfilePage from './pages/ProfilePage';

function Navigation() {
  const navigate = useNavigate();
  const session = getAuthSession();

  return (
    <header className="top-nav">
      <Link to="/" className="brand-link">
        <Logo size="sm" />
      </Link>
      <nav className="top-nav-links">
        <Link to="/">Главная</Link>
        <Link to="/pricing">Тарифы</Link>
        <Link to="/profile">Профиль</Link>
        {!session ? (
          <Link to="/login" className="btn btn-primary btn-nav">
            Вход
          </Link>
        ) : (
          <button
            type="button"
            className="btn btn-ghost btn-nav"
            onClick={async () => {
              await logoutSessionCompletely();
              navigate('/login');
            }}
          >
            Выйти
          </button>
        )}
      </nav>
    </header>
  );
}

function RequireAuth({ children }: { children: ReactElement }) {
  const session = getAuthSession();
  if (!session) {
    return <Navigate to="/login" replace />;
  }
  return children;
}

export default function App() {
  return (
    <div className="app-shell">
      <div className="app-bg" aria-hidden />
      <Navigation />
      <main className="content">
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/pricing" element={<PricingPage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route
            path="/profile"
            element={
              <RequireAuth>
                <ProfilePage />
              </RequireAuth>
            }
          />
          <Route path="*" element={<NotFoundPage />} />
        </Routes>
      </main>
    </div>
  );
}
