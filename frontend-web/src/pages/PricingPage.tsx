import { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  calculateMiniappPrice,
  createMiniappSubscriptionPayment,
  fetchWebPlans,
  type WebDuration,
  type WebPlan,
} from '../lib/api';
import { getAuthSession } from '../lib/auth';

function formatMoney(price: number) {
  return `${new Intl.NumberFormat('ru-RU').format(price)} ₽`;
}

function formatPricePerMonth(price: number) {
  return `${formatMoney(price)} / мес`;
}

export default function PricingPage() {
  const [plans, setPlans] = useState<WebPlan[]>([]);
  const [durations, setDurations] = useState<WebDuration[]>([]);
  const [selectedPlanId, setSelectedPlanId] = useState<number | null>(null);
  const [selectedDurationId, setSelectedDurationId] = useState<number | null>(null);
  const [finalPrice, setFinalPrice] = useState(0);
  const [fullPrice, setFullPrice] = useState(0);
  const [discountAmount, setDiscountAmount] = useState(0);
  const [isCreatingPayment, setIsCreatingPayment] = useState(false);
  const [isCalculatingPrice, setIsCalculatingPrice] = useState(false);
  const [actionError, setActionError] = useState<string | null>(null);
  const session = getAuthSession();

  useEffect(() => {
    fetchWebPlans()
      .then((data) => {
        const nextPlans = data.plans ?? [];
        const nextDurations = data.durations ?? [];

        setPlans(nextPlans);
        setDurations(nextDurations);
        setSelectedPlanId(nextPlans[0]?.id ?? null);
        setSelectedDurationId(nextDurations[0]?.id ?? null);
      })
      .catch(() => {
        setPlans([]);
        setDurations([]);
      });
  }, []);

  useEffect(() => {
    if (!selectedPlanId || !selectedDurationId) {
      return;
    }

    let cancelled = false;
    setActionError(null);
    setIsCalculatingPrice(true);

    calculateMiniappPrice(selectedPlanId, selectedDurationId)
      .then((data) => {
        if (cancelled) return;
        setFullPrice(data.old_price ?? 0);
        setFinalPrice(data.discounted_price ?? 0);
        setDiscountAmount(Math.max(0, (data.old_price ?? 0) - (data.discounted_price ?? 0)));
      })
      .catch(() => {
        if (cancelled) return;
        setActionError('Не удалось рассчитать стоимость. Попробуйте позже.');
        setFullPrice(0);
        setFinalPrice(0);
        setDiscountAmount(0);
      })
      .finally(() => {
        if (!cancelled) {
          setIsCalculatingPrice(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [selectedPlanId, selectedDurationId]);

  const maxDiscount = useMemo(
    () => durations.reduce((max, duration) => Math.max(max, duration.discount_percentage || 0), 0),
    [durations],
  );

  const canCreatePayment =
    session?.provider === 'telegram' && !!session.subscription?.token && !!selectedPlanId && !!selectedDurationId;

  const handleCreatePayment = async () => {
    if (!canCreatePayment || !selectedPlanId || !selectedDurationId || !session?.subscription?.token) {
      return;
    }
    setActionError(null);
    setIsCreatingPayment(true);
    try {
      const result = await createMiniappSubscriptionPayment(selectedPlanId, selectedDurationId, session.subscription.token);
      if (result?.payment_url) {
        window.location.href = result.payment_url;
        return;
      }
      setActionError('Платёж создан, но не получена ссылка на оплату.');
    } catch {
      setActionError('Не удалось создать платёж. Проверьте подписку и попробуйте ещё раз.');
    } finally {
      setIsCreatingPayment(false);
    }
  };

  return (
    <section>
      <div className="page-header">
        <h1 className="section-title">Тарифы</h1>
        <p className="section-lead">
          Выберите тариф и период, чтобы оформить подписку.
          {maxDiscount > 0 ? ` Скидки на длительные периоды — до ${maxDiscount}%.` : ''}
        </p>
      </div>

      <div className="cards">
        {plans.map((plan, index) => {
          const isSelected = plan.id === selectedPlanId;
          return (
            <article
              key={plan.id}
              className={`card ${index === 1 ? 'card--accent' : ''} ${isSelected ? 'card--selected' : ''}`}
              role="button"
              tabIndex={0}
              onClick={() => setSelectedPlanId(plan.id)}
              onKeyDown={(event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                  event.preventDefault();
                  setSelectedPlanId(plan.id);
                }
              }}
            >
              <h3>{plan.name}</h3>
              <p style={{ fontSize: '0.9rem' }}>Серверов в тарифе: {plan.servers_count}</p>
              <p className="plan-price">{formatPricePerMonth(plan.price)}</p>
            </article>
          );
        })}
      </div>

      <div className="card pricing-checkout-card" style={{ marginTop: '1rem' }}>
        <h3>Оформление подписки</h3>
        <div className="pricing-checkout-grid">
          <label>
            Период подписки
            <select
              value={selectedDurationId ?? ''}
              onChange={(event) => setSelectedDurationId(Number(event.target.value))}
              disabled={!durations.length}
            >
              {durations.map((duration) => (
                <option key={duration.id} value={duration.id}>
                  {duration.name} — {duration.days} дн. {duration.discount_percentage ? `(−${duration.discount_percentage}%)` : ''}
                </option>
              ))}
            </select>
          </label>

          <div>
            <p className="stat-label">К оплате</p>
            <div className="stat-value">{isCalculatingPrice ? 'Расчёт…' : formatMoney(finalPrice)}</div>
            {discountAmount > 0 && (
              <p className="pricing-discount-note">
                Экономия: {formatMoney(discountAmount)} (без скидки {formatMoney(fullPrice)})
              </p>
            )}
          </div>
        </div>

        {!session ? (
          <div className="hero-actions" style={{ marginTop: '1rem' }}>
            <Link className="btn btn-primary" to="/login">
              Войти и оформить
            </Link>
          </div>
        ) : canCreatePayment ? (
          <div className="hero-actions" style={{ marginTop: '1rem' }}>
            <button className="btn btn-primary" type="button" onClick={handleCreatePayment} disabled={isCreatingPayment || isCalculatingPrice}>
              {isCreatingPayment ? 'Создаём платёж…' : 'Перейти к оплате'}
            </button>
          </div>
        ) : (
          <p className="pricing-discount-note" style={{ marginTop: '1rem' }}>
            Прямое оформление доступно для Telegram-аккаунтов с активной связкой подписки.
          </p>
        )}

        {actionError && <p className="error-text">{actionError}</p>}
      </div>
    </section>
  );
}
