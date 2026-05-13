import React, { useState, useEffect } from 'react';
import { ArrowLeft, ArrowRight, Clock, Percent } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { API_BASE_URL } from '../services/api';
import api from '../services/api';

interface Duration {
  id: number;
  name: string;
  days: number;
  discount_percentage: number;
  is_trial: boolean;
}

interface Plan {
  id: number;
  name: string;
  price: number;
  servers_count: number;
  description: string;
}

interface PriceCalculation {
  success: boolean;
  plan_id: number;
  duration_id: number;
  old_price: number;
  discounted_price: number;
  discount_percent: number;
  plan_name: string;
  duration_name: string;
}

const DurationsPage: React.FC = () => {
  const { user, webApp, isTelegramMode } = useTelegram();
  const [plan, setPlan] = useState<Plan | null>(null);
  const [durations, setDurations] = useState<Duration[]>([]);
  const [selectedDuration, setSelectedDuration] = useState<Duration | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    window.location.hash = '';
  };

  const handleDurationSelect = async (duration: Duration) => {
    setSelectedDuration(duration);
    
    // Сразу переходим к подтверждению заказа
    if (plan) {
      try {
        // Рассчитываем цену
        const response = await api.post('/miniapp/subscription/calculate-price', {
          plan_id: plan.id,
          duration_id: duration.id
        });
        
        const priceCalculation = response.data;
        
        // Переходим к подтверждению заказа с полными данными
        const params = new URLSearchParams({
          planId: plan.id.toString(),
          planName: plan.name,
          planPrice: plan.price.toString(),
          durationId: duration.id.toString(),
          durationName: duration.name,
          durationDays: duration.days.toString(),
          discountPercent: duration.discount_percentage.toString(),
          totalPrice: priceCalculation.discounted_price.toString(),
          totalPriceWithoutDiscount: priceCalculation.old_price.toString()
        });
        
        window.location.hash = `order-confirmation?${params.toString()}`;
      } catch (err) {
        console.error('Error calculating price:', err);
        // В случае ошибки все равно переходим, но без расчета цены
        const params = new URLSearchParams({
          planId: plan.id.toString(),
          planName: plan.name,
          planPrice: plan.price.toString(),
          durationId: duration.id.toString(),
          durationName: duration.name,
          durationDays: duration.days.toString(),
          discountPercent: duration.discount_percentage.toString()
        });
        
        window.location.hash = `order-confirmation?${params.toString()}`;
      }
    }
  };


  const loadData = async () => {
    setIsLoading(true);
    setError(null);
    
    try {
      // Получаем план из URL
      const hash = window.location.hash;
      const planIdMatch = hash.match(/plans\/(\d+)\/durations/);
      
      if (!planIdMatch) {
        throw new Error('Plan ID not found in URL');
      }
      
      const planId = parseInt(planIdMatch[1]);
      
      // Загружаем планы и продолжительности
      const response = await api.get('/miniapp/subscription/plans');
      
      const data = response.data;
      
      // Находим нужный план
      const foundPlan = data.plans.find((p: Plan) => p.id === planId);
      if (!foundPlan) {
        throw new Error('Plan not found');
      }
      
      setPlan(foundPlan);
      setDurations(data.durations);
      
    } catch (err) {
      console.error('Error loading data:', err);
      setError('Не удалось загрузить данные');
    } finally {
      setIsLoading(false);
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

  if (isLoading) {
    return (
      <Layout>
        <div className="py-6">
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span className="ml-3 text-gray-300">Загружаем данные...</span>
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
              onClick={loadData}
              className="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl"
            >
              Попробовать снова
            </button>
          </div>
        </div>
      </Layout>
    );
  }

  if (!plan) {
    return (
      <Layout>
        <div className="py-6">
          <div className="text-center py-12">
            <p className="text-gray-300">План не найден</p>
          </div>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="elderly-friendly" style={{ padding: '20px' }}>
        {/* Header */}
        <div style={{ display: 'flex', alignItems: 'center', marginBottom: '24px' }}>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{ 
              width: 'auto', 
              padding: '12px', 
              marginBottom: '0',
              marginRight: '12px'
            }}
          >
            <ArrowLeft className="w-5 h-5" />
          </button>
          <h1 className="elderly-title" style={{ fontSize: '24px', margin: '0', color: 'var(--text-primary)' }}>
            Выберите продолжительность
          </h1>
        </div>

        {/* Plan Info */}
        <div className="simple-card" style={{ marginBottom: '24px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '16px' }}>
            <div style={{ 
              width: '48px', 
              height: '48px', 
              background: 'var(--gradient-yellow)', 
              borderRadius: '12px', 
              display: 'flex', 
              alignItems: 'center', 
              justifyContent: 'center',
              color: 'var(--text-dark)',
              boxShadow: 'var(--shadow-button)'
            }}>
              <Clock className="w-6 h-6" />
            </div>
            <div>
              <h2 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark)' }}>
                Выберите продолжительность подписки
              </h2>
              <p className="elderly-text" style={{ fontSize: '14px', margin: '0', color: 'var(--text-dark-secondary)' }}>
                План: {plan.name} ({plan.price} руб./месяц)
              </p>
            </div>
          </div>
          <p className="elderly-text" style={{ color: 'var(--text-dark-secondary)' }}>
            Укажите, на какой срок вы хотите оформить подписку. Действует система скидок: чем дольше срок — тем выгоднее!
          </p>
        </div>

        {/* Durations List */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px', marginBottom: '24px' }}>
          {durations
            .sort((a, b) => a.days - b.days) // Сортируем по количеству дней
            .map((duration) => (
            <div
              key={duration.id}
              onClick={() => handleDurationSelect(duration)}
              style={{
                padding: '20px',
                borderRadius: '16px',
                cursor: 'pointer',
                transition: 'all 0.3s ease',
                background: selectedDuration?.id === duration.id 
                  ? 'var(--gradient-yellow)' 
                  : 'var(--bg-card-glass)',
                border: '2px solid',
                borderColor: selectedDuration?.id === duration.id 
                  ? 'var(--accent-yellow)' 
                  : 'var(--border-light)',
                boxShadow: selectedDuration?.id === duration.id 
                  ? 'var(--shadow-hover)' 
                  : 'var(--shadow-card)',
                backdropFilter: 'blur(10px)',
                color: selectedDuration?.id === duration.id 
                  ? 'var(--text-dark)' 
                  : 'var(--text-dark)'
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                <div>
                  <h3 style={{ 
                    fontSize: '18px', 
                    fontWeight: '600', 
                    margin: '0 0 4px 0',
                    color: 'inherit'
                  }}>
                    {duration.name}
                  </h3>
                  <p style={{ 
                    fontSize: '14px', 
                    margin: '0',
                    color: selectedDuration?.id === duration.id 
                      ? 'var(--text-dark-secondary)' 
                      : 'var(--text-dark-secondary)'
                  }}>
                    {duration.days} дней
                  </p>
                </div>
                
                {duration.discount_percentage > 0 && (
                  <div style={{
                    padding: '8px 12px',
                    borderRadius: '20px',
                    fontSize: '14px',
                    fontWeight: '600',
                    background: selectedDuration?.id === duration.id 
                      ? 'var(--text-dark)' 
                      : 'var(--accent-yellow)',
                    color: selectedDuration?.id === duration.id 
                      ? 'var(--accent-yellow)' 
                      : 'var(--text-dark)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '4px',
                    boxShadow: 'var(--shadow-button)'
                  }}>
                    <Percent className="w-4 h-4" />
                    -{duration.discount_percentage}%
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>


      </div>
    </Layout>
  );
};

export default DurationsPage;
