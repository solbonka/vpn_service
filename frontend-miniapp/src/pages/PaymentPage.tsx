import React, { useState, useEffect } from 'react';
import { ArrowLeft, CreditCard, CheckCircle, AlertCircle, AlertTriangle, MessageCircle, Share2 } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { API_BASE_URL } from '../services/api';
import api from '../services/api';
import SharePaymentModal from '../components/SharePaymentModal';

interface PaymentData {
  success: boolean;
  payment_url: string;
  payment_id: string;
  amount: number;
}

const PaymentPage: React.FC = () => {
  const { user, webApp, isTelegramMode, subscription, supportChannel } = useTelegram();
  const [paymentData, setPaymentData] = useState<PaymentData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isCreatingPayment, setIsCreatingPayment] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [planId, setPlanId] = useState<number | null>(null);
  const [durationId, setDurationId] = useState<number | null>(null);
  const [promoCode, setPromoCode] = useState<string | null>(null);
  const [showShareModal, setShowShareModal] = useState(false);
  const [shareUrl, setShareUrl] = useState<string | null>(null);
  const [isCreatingShareLink, setIsCreatingShareLink] = useState(false);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = '';
  };

  const handlePayment = () => {
    if (!paymentData) return;
    
    if (webApp && isTelegramMode) {
      // В Telegram Mini App открываем ссылку
      webApp.openLink(paymentData.payment_url);
    } else {
      // В браузере открываем в новой вкладке
      window.open(paymentData.payment_url, '_blank');
    }
  };

  const handleRequestPayment = async () => {
    if (!paymentData?.payment_id || !subscription?.token) {
      setError('Платеж не создан');
      return;
    }
    
    setIsCreatingShareLink(true);
    
    try {
      const response = await api.post('/miniapp/payment/create-share-link', {
        payment_id: paymentData.payment_id
      }, {
        headers: {
          'X-Sub-Token': subscription.token
        }
      });
      
      if (response.data.success) {
        setShareUrl(response.data.data.share_url);
        setShowShareModal(true);
      }
    } catch (err: any) {
      console.error('Error creating share link:', err);
      setError('Не удалось создать ссылку для оплаты');
    } finally {
      setIsCreatingShareLink(false);
    }
  };

  const handleContactSupport = () => {
    // Убираем @ из начала, если есть
    const channelName = supportChannel.startsWith('@') ? supportChannel.slice(1) : supportChannel;
    const supportUrl = `https://t.me/${channelName}`;
    
    if (webApp && isTelegramMode) {
      webApp.openTelegramLink(supportUrl);
    } else {
      window.open(supportUrl, '_blank');
    }
  };

  const createPayment = async () => {
    if (!planId || !durationId || !subscription?.token) {
      setError('Недостаточно данных для создания платежа');
      return;
    }

    setIsCreatingPayment(true);
    setError(null);
    
    try {
      const requestBody: any = {
        plan_id: planId,
        duration_id: durationId
      };

      // Добавляем промокод если есть
      if (promoCode) {
        requestBody.promo_code = promoCode;
      }

      const response = await api.post('/miniapp/subscription/create-payment', requestBody, {
        headers: {
          'X-Sub-Token': subscription.token
        }
      });
      
      const data = response.data;
      setPaymentData(data);
      
    } catch (err: any) {
      console.error('Error creating payment:', err);
      setError(err.response?.data?.error || err.message || 'Не удалось создать платеж');
    } finally {
      setIsCreatingPayment(false);
    }
  };

  const loadData = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      // Получаем planId, durationId и промокод из URL
      const hash = window.location.hash;
      const match = hash.match(/plans\/(\d+)\/durations\/(\d+)\/payment/);
      
      if (!match) {
        throw new Error('Invalid URL format');
      }
      
      const extractedPlanId = parseInt(match[1]);
      const extractedDurationId = parseInt(match[2]);
      let extractedPromoCode: string | null = null;
      
      // Извлекаем промокод из query параметров
      const hashParts = hash.split('?');
      if (hashParts.length > 1) {
        const urlParams = new URLSearchParams(hashParts[1]);
        const promoCodeParam = urlParams.get('promoCode');
        if (promoCodeParam) {
          extractedPromoCode = promoCodeParam;
          setPromoCode(promoCodeParam);
        }
      }
      
      setPlanId(extractedPlanId);
      setDurationId(extractedDurationId);
      
      // Создаем платеж с промокодом
      await createPaymentWithParams(extractedPlanId, extractedDurationId, extractedPromoCode);
      
    } catch (err: any) {
      console.error('Error loading data:', err);
      setError(err.message || 'Не удалось загрузить данные');
    } finally {
      setIsLoading(false);
    }
  };

  const createPaymentWithParams = async (planIdParam: number, durationIdParam: number, promoCodeParam: string | null) => {
    if (!subscription?.token) {
      setError('Недостаточно данных для создания платежа');
      return;
    }

    setIsCreatingPayment(true);
    setError(null);
    
    try {
      const requestBody: any = {
        plan_id: planIdParam,
        duration_id: durationIdParam
      };

      // Добавляем промокод если есть
      if (promoCodeParam) {
        requestBody.promo_code = promoCodeParam;
      }

      const response = await api.post('/miniapp/subscription/create-payment', requestBody, {
        headers: {
          'X-Sub-Token': subscription.token
        }
      });
      
      const data = response.data;
      setPaymentData(data);
      
    } catch (err: any) {
      console.error('Error creating payment:', err);
      setError(err.response?.data?.error || err.message || 'Не удалось создать платеж');
    } finally {
      setIsCreatingPayment(false);
    }
  };

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    loadData();

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [webApp, isTelegramMode]);

  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  if (isLoading || isCreatingPayment) {
    return (
      <Layout>
        <div className="py-6">
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span className="ml-3 text-gray-300">
              {isCreatingPayment ? 'Создаем платеж...' : 'Загружаем данные...'}
            </span>
          </div>
        </div>
      </Layout>
    );
  }

  if (error) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <div className="text-red-500 text-6xl mb-4">⚠️</div>
            <h2 className="text-white text-xl mb-2">Ошибка</h2>
            <p className="text-gray-300 mb-4">{error}</p>
            <div className="space-y-3">
              <button
                onClick={loadData}
                className="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl"
              >
                Попробовать снова
              </button>
              <button
                onClick={handleBack}
                className="w-full bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl"
              >
                Назад
              </button>
            </div>
          </div>
        </div>
      </Layout>
    );
  }

  if (!paymentData) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <p className="text-gray-300">Данные платежа не найдены</p>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="py-6">
        {/* Header */}
        <div className="flex items-center mb-6">
          <button
            onClick={handleBack}
            className="p-2 text-gray-400 hover:text-gray-200 transition-colors"
          >
            <ArrowLeft className="w-6 h-6" />
          </button>
          <h1 className="text-xl font-bold ml-2 text-white">Оплата подписки</h1>
        </div>

        {/* Payment Card with Button */}
        <div className="mb-6 bg-white bg-opacity-10 rounded-2xl border border-white border-opacity-20 backdrop-blur-sm overflow-hidden">
          {/* Payment Info Section */}
          <div className="p-4">
            <div className="mb-3">
              <h2 className="text-white font-semibold text-lg">💳 Оплата подписки</h2>
            </div>
            
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="text-gray-300">Сумма к оплате:</span>
                <span className="text-white font-bold text-xl">{paymentData.amount} руб.</span>
              </div>
              
              <div className="flex justify-between items-center">
                <span className="text-gray-300">ID платежа:</span>
                <span className="text-white font-mono text-sm">{paymentData.payment_id}</span>
              </div>
            </div>
          </div>

          {/* Payment Buttons Section */}
          <div className="p-4 pt-0 space-y-3">
            <button
              onClick={handlePayment}
              className="w-full bg-yellow-500 hover:bg-yellow-400 text-black px-6 py-5 rounded-xl font-bold flex items-center justify-center gap-3 transition-all duration-200 shadow-2xl hover:shadow-yellow-500/25 transform hover:scale-[1.02] text-xl"
              style={{
                boxShadow: '0 10px 30px rgba(251, 191, 36, 0.3)',
                border: '2px solid rgba(251, 191, 36, 0.5)'
              }}
            >
              <CreditCard className="w-7 h-7" />
              <span>💳 Оплатить {paymentData.amount} руб.</span>
            </button>

            <button
              onClick={handleRequestPayment}
              disabled={isCreatingShareLink}
              className="w-full bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-6 py-4 rounded-xl font-semibold flex items-center justify-center gap-3 transition-all duration-200 border-2 border-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isCreatingShareLink ? (
                <>
                  <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                  <span>Создание ссылки...</span>
                </>
              ) : (
                <>
                  <Share2 className="w-5 h-5" />
                  <span>Попросить оплатить</span>
                </>
              )}
            </button>
          </div>
        </div>


        {/* Support Note */}
        <div className="mt-6 p-4 bg-white bg-opacity-10 rounded-xl border border-white border-opacity-20 backdrop-blur-sm">
          <div>
            <p className="text-gray-300 text-sm leading-relaxed mb-3">
              Если у вас возникли проблемы с оплатой, обратитесь в поддержку.
            </p>
            <button
              onClick={handleContactSupport}
              className="w-full bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center gap-2"
            >
              <MessageCircle className="w-4 h-4" />
              Связаться с поддержкой
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
            После оплаты подписка активируется автоматически. Вы получите уведомление о успешной оплате.
          </p>
        </div>

        {/* Share Payment Modal */}
        {showShareModal && shareUrl && (
          <SharePaymentModal
            isOpen={showShareModal}
            onClose={() => setShowShareModal(false)}
            shareUrl={shareUrl}
          />
        )}
      </div>
    </Layout>
  );
};

export default PaymentPage;
