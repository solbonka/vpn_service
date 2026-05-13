export type AuthSession = {
  provider: 'web_email' | 'telegram';
  accessToken?: string;
  user?: {
    id: string | number;
    email?: string;
    first_name?: string;
    last_name?: string;
    username?: string;
  };
  subscription?: {
    id: number;
    status: string;
    plan?: string | null;
    duration?: number | null;
    end_date?: string | null;
    token?: string | null;
  } | null;
};

const STORAGE_KEY = 'web_auth_session';

export function saveAuthSession(session: AuthSession) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(session));
}

export function getAuthSession(): AuthSession | null {
  const raw = localStorage.getItem(STORAGE_KEY);
  if (!raw) {
    return null;
  }

  try {
    const parsed = JSON.parse(raw) as { provider?: string };
    if (parsed.provider === 'password') {
      localStorage.removeItem(STORAGE_KEY);
      return null;
    }
    return parsed as AuthSession;
  } catch {
    localStorage.removeItem(STORAGE_KEY);
    return null;
  }
}

export function clearAuthSession() {
  localStorage.removeItem(STORAGE_KEY);
}
