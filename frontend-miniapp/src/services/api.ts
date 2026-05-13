import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 
  (import.meta.env.MODE === 'production' 
    ? '/api' 
    : 'http://localhost:8088/api');

const isDev = import.meta.env.MODE === 'development';

// Отладочная информация только в dev режиме
if (isDev) {
  console.log('API_BASE_URL:', API_BASE_URL);
  console.log('VITE_API_BASE_URL:', import.meta.env.VITE_API_BASE_URL);
  console.log('MODE:', import.meta.env.MODE);
}

// Экспортируем базовый URL для использования в других компонентах
export { API_BASE_URL };

const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'ngrok-skip-browser-warning': 'true',
  },
});

// Добавляем токен к запросам
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('subscription_token');
  if (token) {
    config.headers['X-Sub-Token'] = token;
  }
  if (isDev) {
    console.log('Making API request:', config.method?.toUpperCase(), config.url, config.headers);
  }
  return config;
});

// Добавляем обработку ответов
api.interceptors.response.use(
  (response) => {
    if (isDev) {
      console.log('API response:', response.status, response.config.url);
    }
    return response;
  },
  (error) => {
    if (isDev) {
      console.error('API error:', error.response?.status, error.config?.url, error.message);
    }
    return Promise.reject(error);
  }
);

export const miniappApi = {
  // Авторизация через Telegram + получение подписки
  authenticate: (initData: string) => api.post('/miniapp/auth', {}, {
    headers: { 'X-Telegram-Init-Data': initData }
  }),

  // Лотерея
  lottery: {
    getInfo: () => api.get('/miniapp/lottery/info'),
    getTickets: () => api.get('/miniapp/lottery/tickets'),
    getLeaderboard: () => api.get('/miniapp/lottery/leaderboard'),
    getAvailableNumbers: (limit = 10) => api.get(`/miniapp/lottery/available-numbers?limit=${limit}`),
    checkNumberAvailability: (number: string) => api.post('/miniapp/lottery/check-number', { number }),
    createNumberChangePayment: (ticketId: number, newNumber: string) => 
      api.post('/miniapp/lottery/change-number-payment', { ticket_id: ticketId, new_number: newNumber }),
  },

  // Промокоды
  promoCode: {
    validate: (code: string, durationId?: number) => {
      const payload: any = { code };
      if (durationId) {
        payload.duration_id = durationId;
      }
      return api.post('/miniapp/promo-code/validate', payload);
    },
    calculate: (code: string, amount: number, durationId?: number) => {
      const payload: any = { code, amount };
      if (durationId) {
        payload.duration_id = durationId;
      }
      return api.post('/miniapp/promo-code/calculate', payload);
    },
  },
};

export default api;