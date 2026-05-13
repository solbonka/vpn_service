import axios from 'axios';
import { clearAuthSession, getAuthSession } from './auth';

export const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || (import.meta.env.MODE === 'production' ? '/api' : 'http://localhost:8088/api');

export type TelegramWidgetUser = {
  id: number;
  first_name?: string;
  last_name?: string;
  username?: string;
  photo_url?: string;
  auth_date: number;
  hash: string;
};

export type WebAuthUser = {
  id: number;
  email: string;
  name: string | null;
};

export const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'ngrok-skip-browser-warning': 'true',
    Accept: 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const session = getAuthSession();
  if (session?.accessToken && session.provider === 'web_email') {
    config.headers.Authorization = `Bearer ${session.accessToken}`;
  }
  return config;
});

export async function registerWebClient(payload: {
  email: string;
  password: string;
  password_confirmation: string;
  name?: string;
}) {
  const { data } = await api.post<{ token: string; token_type: string; user: WebAuthUser }>('/web/auth/register', payload);
  return data;
}

export async function loginWebClient(email: string, password: string) {
  const { data } = await api.post<{ token: string; token_type: string; user: WebAuthUser }>('/web/auth/login', {
    email,
    password,
  });
  return data;
}

export async function fetchWebClientMe() {
  const { data } = await api.get<{ user: WebAuthUser }>('/web/auth/me');
  return data;
}

export async function logoutWebClient() {
  await api.post('/web/auth/logout');
}

export async function loginByTelegramWidget(telegramUser: TelegramWidgetUser) {
  const { data } = await api.post('/web/auth/telegram', { telegram_user: telegramUser });
  return data;
}

export async function logoutSessionCompletely() {
  const session = getAuthSession();
  if (session?.provider === 'web_email' && session.accessToken) {
    try {
      await logoutWebClient();
    } catch {
      /* сеть / истёкший токен — всё равно чистим локально */
    }
  }
  clearAuthSession();
}

export type WebPlan = {
  id: number;
  name: string;
  price: number;
  servers_count: number;
};

export type WebDuration = {
  id: number;
  name: string;
  days: number;
  discount_percentage: number;
};

export async function fetchWebPlans() {
  const { data } = await api.get<{ success: boolean; plans: WebPlan[]; durations: WebDuration[] }>('/web/subscription/plans');
  return data;
}


export async function calculateMiniappPrice(planId: number, durationId: number) {
  const { data } = await api.post<{ success: boolean; old_price: number; discounted_price: number; discount_percent: number }>(
    '/miniapp/subscription/calculate-price',
    {
      plan_id: planId,
      duration_id: durationId,
    },
  );
  return data;
}

export async function createMiniappSubscriptionPayment(planId: number, durationId: number, subscriptionToken: string) {
  const { data } = await api.post<{ success: boolean; payment_url: string; payment_id: string; amount: number }>(
    '/miniapp/subscription/create-payment',
    {
      plan_id: planId,
      duration_id: durationId,
    },
    {
      headers: {
        'X-Sub-Token': subscriptionToken,
      },
    },
  );
  return data;
}
