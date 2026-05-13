const plans = [
  { id: 'basic', name: 'Базовый', price: '299 ₽ / мес', note: 'Один пользователь' },
  { id: 'plus', name: 'Плюс', price: '499 ₽ / мес', note: 'Расширенные возможности' },
  { id: 'family', name: 'Семейный', price: '799 ₽ / мес', note: 'Несколько устройств' },
];

export default function PricingPage() {
  return (
    <section>
      <div className="page-header">
        <h1 className="section-title">Тарифы</h1>
        <p className="section-lead">Цены ориентировочные — актуальные условия уточняйте при оплате.</p>
      </div>
      <div className="cards">
        {plans.map((plan, index) => (
          <article key={plan.id} className={`card ${index === 1 ? 'card--accent' : ''}`}>
            <h3>{plan.name}</h3>
            <p style={{ fontSize: '0.9rem' }}>{plan.note}</p>
            <p className="plan-price">{plan.price}</p>
          </article>
        ))}
      </div>
    </section>
  );
}
