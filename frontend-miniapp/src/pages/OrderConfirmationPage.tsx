import React, { useState, useEffect } from 'react';
import { ArrowLeft, CreditCard, ArrowRight, Tag, Loader } from 'lucide-react';
import Layout from '../components/Layout';
import { useTelegram } from '../hooks/useTelegram';
import { miniappApi } from '../services/api';

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

const OrderConfirmationPage: React.FC = () => {
  const { user, webApp, isTelegramMode } = useTelegram();
  const [plan, setPlan] = useState<Plan | null>(null);
  const [duration, setDuration] = useState<Duration | null>(null);
  const [priceCalculation, setPriceCalculation] = useState<PriceCalculation | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  
  // Промокод
  const [promoCode, setPromoCode] = useState('');
  const [promoCodeApplied, setPromoCodeApplied] = useState(false);
  const [promoCodeDiscount, setPromoCodeDiscount] = useState<number>(0);
  const [promoCodeError, setPromoCodeError] = useState<string | null>(null);
  const [isValidatingPromoCode, setIsValidatingPromoCode] = useState(false);
  const [finalPrice, setFinalPrice] = useState<number>(0);

  const handleBack = () => {
    if (webApp && isTelegramMode) {
      webApp.BackButton.hide();
    }
    // Возвращаемся к выбору тарифов
    window.location.hash = 'plans';
  };

  const handleApplyPromoCode = async () => {
    if (!promoCode.trim() || !priceCalculation || !duration) {
      setPromoCodeError('Ошибка: нет данных о тарифе');
      return;
    }
    
    const durationIdToSend = duration?.id ? Number(duration.id) : undefined;
    
    if (!durationIdToSend || isNaN(durationIdToSend)) {
      setPromoCodeError(`Ошибка: duration.id = ${duration?.id}, parsed = ${durationIdToSend}`);
      return;
    }
    
    setIsValidatingPromoCode(true);
    setPromoCodeError(null);
    
    try {
      const response = await miniappApi.promoCode.validate(promoCode.trim().toUpperCase(), durationIdToSend);
      
      if (response.data.success) {
        const discountPercent = response.data.data.discount_percent;
        setPromoCodeDiscount(discountPercent);
        setPromoCodeApplied(true);
        
        // Рассчитываем финальную цену
        const originalPrice = priceCalculation.discounted_price;
        const discountAmount = (originalPrice * discountPercent) / 100;
        const newFinalPrice = Math.round(originalPrice - discountAmount);
        setFinalPrice(newFinalPrice);
        
        if (webApp && isTelegramMode) {
          webApp.HapticFeedback.notificationOccurred('success');
        }
      }
    } catch (err: any) {
      const errorMessage = err.response?.data?.error || 'Ошибка применения промокода';
      setPromoCodeError(errorMessage);
      setPromoCodeApplied(false);
      setPromoCodeDiscount(0);
      setFinalPrice(priceCalculation?.discounted_price || 0);
      
      if (webApp && isTelegramMode) {
        webApp.HapticFeedback.notificationOccurred('error');
      }
    } finally {
      setIsValidatingPromoCode(false);
    }
  };

  const handleRemovePromoCode = () => {
    setPromoCode('');
    setPromoCodeApplied(false);
    setPromoCodeDiscount(0);
    setPromoCodeError(null);
    setFinalPrice(priceCalculation?.discounted_price || 0);
  };

  const handleProceedToPayment = () => {
    if (!plan || !duration) return;

    if (webApp && isTelegramMode) {
      webApp.HapticFeedback.impactOccurred('light');
    }
    
    // Переходим к странице оплаты с промокодом
    const params = new URLSearchParams();
    if (promoCodeApplied && promoCode) {
      params.append('promoCode', promoCode.trim().toUpperCase());
    }
    const queryString = params.toString();
    window.location.hash = `plans/${plan.id}/durations/${duration.id}/payment${queryString ? '?' + queryString : ''}`;
  };

  // Загружаем данные из URL параметров
  useEffect(() => {
    const loadOrderData = () => {
      try {
        // Получаем параметры из хеша, а не из search
        const hash = window.location.hash; // например: #order-confirmation?planId=1&...
        const hashParts = hash.split('?');
        let queryString = '';
        if (hashParts.length > 1) {
          queryString = hashParts[1]; // получаем только часть с параметрами
        }
        const urlParams = new URLSearchParams(queryString);
        
        // Получаем все необходимые параметры
        const planId = urlParams.get('planId');
        const planName = urlParams.get('planName');
        const planPrice = urlParams.get('planPrice');
        const durationId = urlParams.get('durationId');
        const durationName = urlParams.get('durationName');
        const durationDays = urlParams.get('durationDays');
        const discountPercent = urlParams.get('discountPercent');
        const totalPrice = urlParams.get('totalPrice');
        const totalPriceWithoutDiscount = urlParams.get('totalPriceWithoutDiscount');

        // Проверяем наличие всех необходимых параметров
        if (!planId || !planName || !planPrice || !durationId || !durationName || 
            !durationDays || discountPercent === null || !totalPrice || !totalPriceWithoutDiscount) {
          setError('Неверные параметры заказа');
          setIsLoading(false);
          return;
        }

        // Создаем объекты на основе переданных параметров
        const orderPlan: Plan = {
          id: parseInt(planId),
          name: planName,
          price: parseInt(planPrice),
          servers_count: 1,
          description: 'Базовый план VPN'
        };

        const orderDuration: Duration = {
          id: parseInt(durationId),
          name: durationName,
          days: parseInt(durationDays),
          discount_percentage: parseInt(discountPercent),
          is_trial: false
        };

        const orderPriceCalculation: PriceCalculation = {
          success: true,
          plan_id: orderPlan.id,
          duration_id: orderDuration.id,
          old_price: parseInt(totalPriceWithoutDiscount),
          discounted_price: parseInt(totalPrice),
          discount_percent: orderDuration.discount_percentage,
          plan_name: orderPlan.name,
          duration_name: orderDuration.name
        };

        setPlan(orderPlan);
        setDuration(orderDuration);
        setPriceCalculation(orderPriceCalculation);
        setFinalPrice(orderPriceCalculation.discounted_price);

      } catch (err) {
        console.error('Error loading order data:', err);
        setError('Ошибка загрузки данных заказа');
      } finally {
        setIsLoading(false);
      }
    };

    loadOrderData();

    if (webApp && isTelegramMode) {
      webApp.BackButton.show();
      webApp.BackButton.onClick(handleBack);
    }

    return () => {
      if (webApp && isTelegramMode) {
        webApp.BackButton.hide();
      }
    };
  }, []);

  if (isLoading) {
    return (
      <Layout>
        <div style={{ 
          display: 'flex', 
          justifyContent: 'center', 
          alignItems: 'center', 
          minHeight: '50vh',
          flexDirection: 'column',
          gap: '16px'
        }}>
          <div style={{ 
            width: '40px', 
            height: '40px', 
            border: '3px solid var(--accent-yellow)', 
            borderTop: '3px solid transparent',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite'
          }}></div>
          <p className="elderly-text" style={{ color: 'var(--text-primary)' }}>
            Загружаем данные заказа...
          </p>
        </div>
      </Layout>
    );
  }

  if (error || !plan || !duration || !priceCalculation) {
    return (
      <Layout>
        <div className="elderly-friendly" style={{ padding: '20px', textAlign: 'center' }}>
          <h2 className="elderly-title" style={{ color: 'var(--text-primary)', marginBottom: '16px' }}>
            Ошибка
          </h2>
          <p className="elderly-text" style={{ color: 'var(--text-secondary)', marginBottom: '24px' }}>
            {error || 'Не удалось загрузить данные заказа'}
          </p>
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-primary"
            style={{ width: 'auto', padding: '12px 24px' }}
          >
            Назад
          </button>
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
            Подтверждение заказа
          </h1>
        </div>

        {/* Order Summary */}
        <div className="simple-card" style={{ marginBottom: '24px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '20px' }}>
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
              <span style={{ fontSize: '20px' }}>💰</span>
            </div>
            <h2 className="elderly-subtitle" style={{ margin: '0', color: 'var(--text-dark-strong)' }}>
              Итоговая стоимость
            </h2>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
            {/* Plan */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span className="elderly-text" style={{ color: 'var(--text-dark)' }}>
                План:
              </span>
              <span className="elderly-text" style={{ color: 'var(--text-dark-strong)', fontWeight: '600' }}>
                {plan.name}
              </span>
            </div>

            {/* Duration */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span className="elderly-text" style={{ color: 'var(--text-dark)' }}>
                Продолжительность:
              </span>
              <span className="elderly-text" style={{ color: 'var(--text-dark-strong)', fontWeight: '600' }}>
                {duration.name}
              </span>
            </div>

            {/* Divider */}
            <div style={{ 
              height: '1px', 
              background: 'var(--border-light)', 
              margin: '8px 0' 
            }}></div>

            {/* Total Price */}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <span className="elderly-text-large" style={{ 
                color: 'var(--text-dark-strong)', 
                fontWeight: '600',
                fontSize: '18px'
              }}>
                К оплате:
              </span>
              <span style={{ 
                fontSize: '24px', 
                fontWeight: '700',
                color: 'var(--text-dark-strong)'
              }}>
                {finalPrice} руб.
              </span>
            </div>

            {/* Discount Info */}
            <div style={{ textAlign: 'right', display: 'flex', flexDirection: 'column', gap: '4px' }}>
              {priceCalculation.discount_percent > 0 && (
                <div>
                  <span style={{ 
                    fontSize: '14px', 
                    color: 'var(--text-dark-secondary)',
                    textDecoration: 'line-through'
                  }}>
                    {priceCalculation.old_price} руб.
                  </span>
                  <span style={{ 
                    fontSize: '14px', 
                    color: 'var(--accent-yellow)',
                    fontWeight: '600',
                    marginLeft: '8px'
                  }}>
                    -{priceCalculation.discount_percent}%
                  </span>
                </div>
              )}
              {promoCodeApplied && (
                <div>
                  <span style={{ 
                    fontSize: '14px', 
                    color: 'var(--text-dark-secondary)',
                    textDecoration: 'line-through'
                  }}>
                    {priceCalculation.discounted_price} руб.
                  </span>
                  <span style={{ 
                    fontSize: '14px', 
                    color: '#22c55e',
                    fontWeight: '600',
                    marginLeft: '8px'
                  }}>
                    -{promoCodeDiscount}% (промокод)
                  </span>
                </div>
              )}
            </div>
          </div>
        </div>

        {/* Promo Code Section */}
        <div className="simple-card" style={{ marginBottom: '24px' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '16px' }}>
            <Tag size={20} style={{ color: 'var(--text-dark)' }} />
            <span className="elderly-text" style={{ color: 'var(--text-dark-strong)', fontWeight: '600' }}>
              Есть промокод?
            </span>
          </div>

          {!promoCodeApplied ? (
            <>
              <div style={{ marginBottom: '12px' }}>
                <input
                  type="text"
                  value={promoCode}
                  onChange={(e) => setPromoCode(e.target.value.toUpperCase())}
                  placeholder="ВВЕДИТЕ КОД"
                  disabled={isValidatingPromoCode}
                  style={{
                    width: '100%',
                    padding: '14px 16px',
                    border: promoCodeError ? '2px solid #ef4444' : '2px solid #ddd',
                    borderRadius: '12px',
                    fontSize: '17px',
                    fontFamily: 'monospace',
                    letterSpacing: '3px',
                    textTransform: 'uppercase',
                    backgroundColor: 'white',
                    color: '#1a1a1a',
                    textAlign: 'center',
                    fontWeight: '600',
                    boxSizing: 'border-box',
                    outline: 'none'
                  }}
                  onKeyPress={(e) => e.key === 'Enter' && handleApplyPromoCode()}
                />
              </div>
              <button
                onClick={handleApplyPromoCode}
                disabled={!promoCode.trim() || isValidatingPromoCode}
                className="vpn-button vpn-button-primary"
                style={{
                  width: '100%',
                  padding: '14px',
                  marginBottom: '0',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  gap: '8px',
                  fontSize: '16px',
                  fontWeight: '600'
                }}
              >
                {isValidatingPromoCode ? (
                  <>
                    <Loader size={20} style={{ animation: 'spin 1s linear infinite' }} />
                    <span>Проверка...</span>
                  </>
                ) : (
                  <>
                    <Tag size={20} />
                    <span>Применить промокод</span>
                  </>
                )}
              </button>
              {promoCodeError && (
                <p style={{ 
                  color: '#ef4444', 
                  fontSize: '14px', 
                  margin: '12px 0 0 0',
                  textAlign: 'center',
                  fontWeight: '500'
                }}>
                  {promoCodeError}
                </p>
              )}
            </>
          ) : (
            <div style={{ 
              display: 'flex', 
              justifyContent: 'space-between', 
              alignItems: 'center',
              padding: '12px 16px',
              backgroundColor: 'rgba(34, 197, 94, 0.1)',
              borderRadius: '12px',
              border: '2px solid #22c55e'
            }}>
              <div>
                <span style={{ 
                  fontFamily: 'monospace', 
                  fontSize: '16px', 
                  fontWeight: '600',
                  letterSpacing: '2px',
                  color: '#22c55e'
                }}>
                  {promoCode}
                </span>
                <span style={{ 
                  fontSize: '14px', 
                  color: 'var(--text-dark)',
                  marginLeft: '12px'
                }}>
                  -{promoCodeDiscount}%
                </span>
              </div>
              <button
                onClick={handleRemovePromoCode}
                style={{
                  background: 'none',
                  border: 'none',
                  color: '#ef4444',
                  fontSize: '14px',
                  fontWeight: '600',
                  cursor: 'pointer',
                  padding: '4px 8px'
                }}
              >
                Удалить
              </button>
            </div>
          )}
        </div>

        {/* Action Buttons */}
        <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
          {/* Proceed to Payment Button */}
          <button
            onClick={handleProceedToPayment}
            className="vpn-button vpn-button-primary"
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '12px',
              fontSize: '16px'
            }}
          >
            <CreditCard className="w-5 h-5" />
            <span>Перейти к оплате</span>
            <ArrowRight className="w-5 h-5" />
          </button>

          {/* Back Button */}
          <button
            onClick={handleBack}
            className="vpn-button vpn-button-secondary"
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              gap: '12px',
              fontSize: '16px'
            }}
          >
            <ArrowLeft className="w-5 h-5" />
            <span>Вернуться к выбору тарифов</span>
          </button>
        </div>

      </div>
    </Layout>
  );
};

export default OrderConfirmationPage;
