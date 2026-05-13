import React, { useState, useEffect } from 'react';
import { ArrowLeft, CheckCircle, AlertCircle, RefreshCw } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { API_BASE_URL } from '../services/api';
import api from '../services/api';

interface ActivationResponse {
  success: boolean;
  message: string;
}

const ActivateSubscriptionPage: React.FC = () => {
  const { user, webApp, isTelegramMode, subscription } = useTelegram();
  const [isLoading, setIsLoading] = useState(true);
  const [isActivating, setIsActivating] = useState(false);
  const [result, setResult] = useState<ActivationResponse | null>(null);
  const [error, setError] = useState<string | null>(null);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = '';
  };

  const activateSubscription = async () => {
    if (!subscription?.token) {
      setError('Токен подписки не найден');
      return;
    }

    setIsActivating(true);
    setError(null);
    
    try {
      const response = await api.post('/miniapp/subscription/activate', {}, {
        headers: {
          'X-Sub-Token': subscription.token
        }
      });
      
      const data = response.data;
      setResult(data);
      
    } catch (err: any) {
      console.error('Error activating subscription:', err);
      setError(err.message || 'Не удалось активировать подписку');
    } finally {
      setIsActivating(false);
    }
  };

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    // Устанавливаем isLoading в false после инициализации
    setIsLoading(false);

    // Автоматически активируем подписку при загрузке страницы
    activateSubscription();

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [webApp, isTelegramMode]);

  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  if (isLoading || isActivating) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px' }}>
          <div style={{ 
            textAlign: 'center',
            padding: '40px 20px',
            marginBottom: '30px',
            width: '100%'
          }}>
            <div style={{ 
              width: '48px', 
              height: '48px', 
              border: '4px solid var(--border-light)', 
              borderTop: '4px solid var(--primary-blue)', 
              borderRadius: '50%', 
              animation: 'spin 1s linear infinite',
              margin: '0 auto 16px'
            }}></div>
            <p style={{ 
              fontSize: '18px',
              lineHeight: '1.7',
              color: 'var(--text-primary)'
            }}>
              {isActivating ? 'Активируем подписку...' : 'Загружаем данные...'}
            </p>
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
                onClick={activateSubscription}
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

  if (result && !result.success) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px' }}>
          <div style={{ 
            textAlign: 'center',
            padding: '40px 20px',
            marginBottom: '30px',
            width: '100%'
          }}>
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>⚠️</div>
            <h2 style={{ 
              fontSize: '24px',
              fontWeight: '700',
              color: 'var(--accent-yellow)',
              marginBottom: '12px',
              lineHeight: '1.4'
            }}>
              Подписка активна
            </h2>
            <p style={{ 
              fontSize: '18px',
              lineHeight: '1.7',
              color: 'var(--text-primary)',
              marginBottom: '24px'
            }}>
              {result.message}
            </p>
            <div style={{ display: 'flex', justifyContent: 'center' }}>
              <button
                onClick={handleBack}
                className="elderly-button"
                style={{ maxWidth: '200px' }}
              >
                Назад
              </button>
            </div>
          </div>
        </div>
      </Layout>
    );
  }

  if (result && result.success) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px' }}>
          <div style={{ 
            textAlign: 'center',
            padding: '40px 20px',
            marginBottom: '30px',
            width: '100%'
          }}>
            <div style={{ fontSize: '48px', marginBottom: '16px' }}>✅</div>
            <h2 style={{ 
              fontSize: '24px',
              fontWeight: '700',
              color: 'var(--accent-yellow)',
              marginBottom: '12px',
              lineHeight: '1.4'
            }}>
              Подписка активирована!
            </h2>
            <p style={{ 
              fontSize: '18px',
              lineHeight: '1.7',
              color: 'var(--text-primary)',
              marginBottom: '24px'
            }}>
              {result.message}
            </p>
            
            <div style={{ display: 'flex', justifyContent: 'center' }}>
              <button
                onClick={handleBack}
                className="elderly-button"
                style={{ maxWidth: '200px' }}
              >
                Назад
              </button>
            </div>
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
          <h1 className="text-xl font-bold ml-2 text-white">Активация подписки</h1>
        </div>

        {/* Info */}
        <div className="mb-6 p-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <RefreshCw className="w-6 h-6 text-white" />
            </div>
            <h2 className="text-white font-semibold text-lg">🔄 Активация подписки</h2>
          </div>
          
          <p className="text-blue-100 text-sm">
            Ваша подписка будет активирована автоматически. Это может занять несколько секунд.
          </p>
        </div>

        {/* Status */}
        <div className="mb-6 p-4 bg-gradient-to-r from-gray-800 to-gray-700 rounded-2xl">
          <div className="flex items-center gap-3 mb-2">
            <AlertCircle className="w-5 h-5 text-yellow-400" />
            <h3 className="text-white font-semibold">ℹ️ Информация</h3>
          </div>
          <p className="text-gray-300 text-sm">
            После активации подписки вы сможете подключиться к VPN и использовать все доступные серверы.
          </p>
        </div>

        {/* Manual Activation Button */}
        <button
          onClick={activateSubscription}
          disabled={isActivating}
          className="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:from-gray-600 disabled:to-gray-700 text-white px-6 py-4 rounded-2xl font-semibold flex items-center justify-center gap-3 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02] disabled:transform-none"
        >
          {isActivating ? (
            <>
              <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
              <span>Активируем...</span>
            </>
          ) : (
            <>
              <RefreshCw className="w-5 h-5" />
              <span>Активировать подписку</span>
            </>
          )}
        </button>
      </div>
    </Layout>
  );
};

export default ActivateSubscriptionPage;
