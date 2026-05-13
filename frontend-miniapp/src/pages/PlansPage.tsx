import React, { useState, useEffect } from 'react';
import { ArrowLeft, Check, Globe } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { API_BASE_URL } from '../services/api';
import api from '../services/api';

interface Plan {
  id: number;
  name: string;
  price: number;
  servers_count: number;
  description: string;
}

interface PlansData {
  success: boolean;
  payment_enabled: boolean;
  plans: Plan[];
  has_multiple_plans: boolean;
}

const PlansPage: React.FC = () => {
  const { user, webApp, isTelegramMode } = useTelegram();
  const [plansData, setPlansData] = useState<PlansData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = '';
  };

  const handlePlanSelect = (planId: number) => {
    window.location.hash = `plans/${planId}/durations`;
  };

  const loadPlans = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      const response = await api.get('/miniapp/subscription/plans');
      
      const data = response.data;
      setPlansData(data);
      
    } catch (err) {
      console.error('Error loading plans:', err);
      setError('Не удалось загрузить планы подписки');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    loadPlans();

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, [webApp, isTelegramMode]);

  if (!user) return <Layout><div>Loading user data...</div></Layout>;

  if (isLoading) {
    return (
      <Layout>
        <div className="py-6">
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span className="ml-3 text-gray-300">Загружаем планы...</span>
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
            <button
              onClick={loadPlans}
              className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl"
            >
              Попробовать снова
            </button>
          </div>
        </div>
      </Layout>
    );
  }

  if (!plansData) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <p className="text-gray-300">Нет доступных планов</p>
          </div>
        </div>
      </Layout>
    );
  }

  // Если платежи отключены, показываем сообщение
  if (!plansData.payment_enabled) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <div className="text-yellow-500 text-6xl mb-4">⚠️</div>
            <h2 className="text-white text-xl mb-2">Платежи отключены</h2>
            <p className="text-gray-300 mb-4">
              В данный момент оплата подписок недоступна. Обратитесь в поддержку.
            </p>
            <button
              onClick={handleBack}
              className="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-xl"
            >
              Назад
            </button>
          </div>
        </div>
      </Layout>
    );
  }

  // Если план один, сразу переходим к выбору продолжительности
  if (!plansData.has_multiple_plans && plansData.plans.length === 1) {
    const plan = plansData.plans[0];
    handlePlanSelect(plan.id);
    return null;
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
          <h1 className="text-xl font-bold ml-2 text-white">Выберите тарифный план</h1>
        </div>

        {/* Description */}
        <div className="mb-6 p-4 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl">
          <div className="flex items-center gap-3 mb-2">
            <div className="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
              <Globe className="w-5 h-5 text-white" />
            </div>
            <h2 className="text-white font-semibold text-lg">💼 Выберите тарифный план</h2>
          </div>
          <p className="text-blue-100 text-sm">
            Пожалуйста, выберите подходящий тариф из списка ниже.
          </p>
          <p className="text-blue-100 text-sm mt-2">
            🌍 <strong>Регионы</strong> — это географические локации серверов. Больше регионов означает больше возможностей для стабильного подключения.
          </p>
        </div>

        {/* Plans List */}
        <div className="space-y-4">
          {plansData.plans.map((plan) => (
            <div
              key={plan.id}
              onClick={() => handlePlanSelect(plan.id)}
              className="bg-gradient-to-r from-gray-800 to-gray-700 rounded-2xl p-6 cursor-pointer transition-all duration-200 hover:from-gray-700 hover:to-gray-600 hover:scale-[1.02] shadow-lg hover:shadow-xl"
            >
              <div className="flex items-center justify-between mb-3">
                <h3 className="text-white font-bold text-xl">{plan.name}</h3>
                <div className="text-right">
                  <div className="text-green-400 font-bold text-lg">{plan.price} руб.</div>
                  <div className="text-gray-400 text-sm">за месяц</div>
                </div>
              </div>
              
              <p className="text-gray-300 text-sm mb-4">{plan.description}</p>
              
              <div className="flex items-center gap-2 text-blue-400 text-sm">
                <Check className="w-4 h-4" />
                <span>Доступ к {plan.servers_count} серверам</span>
              </div>
            </div>
          ))}
        </div>

        {/* Info */}
        <div className="mt-6 p-4 bg-gradient-to-r from-gray-800 to-gray-700 rounded-2xl">
          <h3 className="text-white font-semibold mb-2">ℹ️ Информация</h3>
          <p className="text-gray-300 text-sm">
            После выбора плана вы сможете выбрать продолжительность подписки с учетом скидок.
          </p>
        </div>
      </div>
    </Layout>
  );
};

export default PlansPage;
