import React, { useState, useEffect } from 'react';
import { CreditCard, AlertCircle, CheckCircle, Loader } from 'lucide-react';
import Layout from '../components/Layout';
import api from '../services/api';

// Типы для Telegram WebApp
declare global {
  interface Window {
    Telegram?: {
      WebApp: any;
    };
  }
}

interface PaymentInfo {
  payment_id: number;
  yookassa_payment_id: string;
  payment_url: string;
  amount: number;
  currency: string;
  status: string;
  is_payable: boolean;
  plan: {
    id: number;
    name: string;
  };
  duration: {
    id: number;
    name: string;
  };
}

const SharedPaymentPage: React.FC = () => {
  const [paymentInfo, setPaymentInfo] = useState<PaymentInfo | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Проверяем доступность Telegram WebApp (опционально)
  const webApp = window.Telegram?.WebApp || null;
  const isTelegramMode = !!webApp;

  // Получаем токен из URL hash (например: #/pay/abc123)
  const getTokenFromHash = () => {
    const hash = window.location.hash; // #/pay/abc123
    const match = hash.match(/#?\/?pay\/(.+)$/); // поддерживает #/pay/ и /pay/ и pay/
    return match ? match[1] : null;
  };

  useEffect(() => {
    loadPaymentInfo();
  }, []);

  const loadPaymentInfo = async () => {
    const token = getTokenFromHash();
    
    if (!token) {
      setError('Неверная ссылка');
      setIsLoading(false);
      return;
    }

    try {
      const response = await api.get(`/payment/share/${token}`);
      
      if (response.data.success) {
        setPaymentInfo(response.data.data);
      } else {
        setError('Ссылка недействительна');
      }
    } catch (err: any) {
      console.error('Error loading payment info:', err);
      setError(err.response?.data?.error || 'Не удалось загрузить информацию об оплате');
    } finally {
      setIsLoading(false);
    }
  };

  const handlePayment = () => {
    if (!paymentInfo?.payment_url) return;
    
    if (webApp && isTelegramMode) {
      webApp.openLink(paymentInfo.payment_url);
    } else {
      window.open(paymentInfo.payment_url, '_blank');
    }
  };

  if (isLoading) {
    return (
      <Layout>
        <div className="py-6">
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span className="ml-3 text-gray-300">Загрузка...</span>
          </div>
        </div>
      </Layout>
    );
  }

  if (error || !paymentInfo) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <div className="text-red-500 text-6xl mb-4">⚠️</div>
            <h2 className="text-white text-xl mb-2">Ошибка</h2>
            <p className="text-gray-300 mb-4">{error || 'Ссылка недействительна'}</p>
          </div>
        </div>
      </Layout>
    );
  }

  // Если платеж уже оплачен
  if (!paymentInfo.is_payable) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <div className="text-green-500 text-6xl mb-4">✅</div>
            <h2 className="text-white text-xl mb-2">Уже оплачено</h2>
            <p className="text-gray-300">Этот платеж уже был успешно оплачен</p>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="py-6">
        {/* Header */}
        <div className="mb-6">
          <h1 className="text-xl font-bold text-white">Оплата подписки</h1>
        </div>

        {/* Request Notice Banner */}
        <div className="mb-6 bg-blue-600 rounded-2xl p-4 border-2 border-blue-400 shadow-xl">
          <div className="flex items-start gap-3">
            <div className="flex-shrink-0 w-10 h-10 bg-white rounded-full flex items-center justify-center">
              <span className="text-2xl">🤝</span>
            </div>
            <div className="flex-1">
              <h3 className="text-white font-bold text-base mb-1">Запрос на оплату</h3>
              <p className="text-white text-sm opacity-90">
                Вас попросили оплатить VPN подписку. После оплаты доступ получит владелец аккаунта.
              </p>
            </div>
          </div>
        </div>

        {/* Payment Card */}
        <div className="mb-6 bg-white bg-opacity-10 rounded-2xl border border-white border-opacity-20 backdrop-blur-sm overflow-hidden">
          {/* Payment Info Section */}
          <div className="p-4">
            <div className="mb-3">
              <h2 className="text-white font-semibold text-lg">💳 Оплата подписки</h2>
            </div>
            
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="text-gray-300">Тариф:</span>
                <span className="text-white font-semibold">{paymentInfo.plan.name}</span>
              </div>
              
              {paymentInfo.duration.name && (
                <div className="flex justify-between items-center">
                  <span className="text-gray-300">Длительность:</span>
                  <span className="text-white font-semibold">{paymentInfo.duration.name}</span>
                </div>
              )}
              
              <div className="flex justify-between items-center pt-2 border-t border-white border-opacity-20">
                <span className="text-gray-300">Сумма к оплате:</span>
                <span className="text-white font-bold text-xl">{paymentInfo.amount} ₽</span>
              </div>
            </div>
          </div>

          {/* Payment Button Section */}
          <div className="p-4 pt-0">
            <button
              onClick={handlePayment}
              className="w-full bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-5 rounded-xl font-bold flex items-center justify-center gap-3 transition-all duration-200 shadow-2xl hover:shadow-yellow-500/25 transform hover:scale-[1.02] text-xl"
              style={{
                boxShadow: '0 10px 30px rgba(251, 191, 36, 0.3)',
                border: '2px solid rgba(251, 191, 36, 0.5)'
              }}
            >
              <CreditCard className="w-7 h-7" />
              <span>💳 Оплатить {paymentInfo.amount} ₽</span>
            </button>
          </div>
        </div>

        {/* Info */}
        <div className="mt-4 p-4 bg-white bg-opacity-10 rounded-xl border border-white border-opacity-20">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center flex-shrink-0">
              <span className="text-white text-sm font-bold">i</span>
            </div>
            <h3 className="text-white font-semibold">Информация</h3>
          </div>
          <p className="text-gray-300 text-sm leading-relaxed">
            После оплаты подписка будет активирована автоматически для владельца аккаунта
          </p>
        </div>
      </div>
    </Layout>
  );
};

export default SharedPaymentPage;

