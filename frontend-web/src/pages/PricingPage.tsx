import { useEffect, useMemo, useState } from 'react';
import { fetchWebPlans, type WebDuration, type WebPlan } from '../lib/api';

function formatPricePerMonth(price: number) {
  return `${new Intl.NumberFormat('ru-RU').format(price)} ₽ / мес`;
}

export default function PricingPage() {
  const [plans, setPlans] = useState<WebPlan[]>([]);
  const [durations, setDurations] = useState<WebDuration[]>([]);

  useEffect(() => {
    fetchWebPlans()
      .then((data) => {
        setPlans(data.plans ?? []);
        setDurations(data.durations ?? []);
      })
      .catch(() => {
        setPlans([]);
        setDurations([]);
      });
  }, []);

  const maxDiscount = useMemo(
    () => durations.reduce((max, duration) => Math.max(max, duration.discount_percentage || 0), 0),
    [durations],
  );

  return (
    <section>
      <div className="page-header">
        <h1 className="section-title">Тарифы</h1>
        <p className="section-lead">
          Актуальные тарифы загружаются из бэкенда.
          {maxDiscount > 0 ? ` Скидки на продление — до ${maxDiscount}%.` : ''}
        </p>
      </div>
      <div className="cards">
        {plans.map((plan, index) => (
          <article key={plan.id} className={`card ${index === 1 ? 'card--accent' : ''}`}>
            <h3>{plan.name}</h3>
            <p style={{ fontSize: '0.9rem' }}>Серверов в тарифе: {plan.servers_count}</p>
            <p className="plan-price">{formatPricePerMonth(plan.price)}</p>
          </article>
        ))}
      </div>
    </section>
  );
}
