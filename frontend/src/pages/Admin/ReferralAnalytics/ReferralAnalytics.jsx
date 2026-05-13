import React, { useState, useEffect } from 'react';
import { TrendingUp, Users, DollarSign, Trophy, Percent, Gift } from 'lucide-react';
import api from '../../../api';

function ReferralAnalytics() {
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [dashboardData, setDashboardData] = useState(null);
  const [period, setPeriod] = useState('month');
  const [limit, setLimit] = useState(6);
  const [activeTab, setActiveTab] = useState('overview');

  const fetchDashboardData = async () => {
    try {
      setLoading(true);
      setError(null);
      
      console.log('Fetching data with period:', period, 'limit:', limit);
      
      const response = await api.get('/admin/referral-analytics/dashboard', {
        params: { period, limit }
      });
      
      console.log('API response:', response.data);
      
      if (response.data.success) {
        setDashboardData(response.data.data);
      } else {
        setError(response.data.message || 'Ошибка при загрузке данных');
      }
    } catch (err) {
      console.error('API error:', err);
      setError(err.response?.data?.message || 'Ошибка при загрузке данных');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDashboardData();
  }, [period, limit]);

  const formatCurrency = (value) => {
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 0,
    }).format(value);
  };

  const StatCard = ({ title, value, icon, color = '#2563eb' }) => (
    <div style={{
      backgroundColor: '#1f2937',
      borderRadius: '12px',
      padding: '24px',
      border: '1px solid #374151',
      display: 'flex',
      alignItems: 'center',
      gap: '16px'
    }}>
      <div style={{
        padding: '12px',
        borderRadius: '50%',
        backgroundColor: `${color}20`,
        color: color
      }}>
        {icon}
      </div>
      <div>
        <p style={{ color: '#9ca3af', fontSize: '14px', margin: '0 0 4px 0' }}>{title}</p>
        <p style={{ color: 'white', fontSize: '24px', fontWeight: 'bold', margin: 0 }}>{value}</p>
      </div>
    </div>
  );

  const SimpleChart = ({ data, dataKey, title, period = 'month' }) => {
    // Проверяем, что data является массивом и не пустой
    if (!Array.isArray(data) || data.length === 0) {
      return (
        <div style={{
          backgroundColor: '#1f2937',
          borderRadius: '12px',
          padding: '24px',
          border: '1px solid #374151',
          textAlign: 'center'
        }}>
          <h3 style={{ color: 'white', fontSize: '18px', fontWeight: '600', marginBottom: '20px' }}>{title}</h3>
          <p style={{ color: '#9ca3af' }}>Нет данных для отображения</p>
        </div>
      );
    }

    const maxValue = Math.max(...data.map(d => Number(d[dataKey] || 0)));
    const [hoveredIndex, setHoveredIndex] = useState(null);
    
    // Адаптивные настройки в зависимости от периода
    const getChartConfig = () => {
      const dataLength = data.length;
      
      switch (period) {
        case 'day':
          return {
            height: '250px',
            gap: dataLength <= 3 ? '15px' : dataLength <= 7 ? '8px' : '2px',
            barWidth: '100%',
            fontSize: dataLength <= 3 ? '12px' : dataLength <= 7 ? '10px' : '8px',
            maxBars: 30,
            minBarWidth: dataLength <= 3 ? '60px' : dataLength <= 7 ? '40px' : '20px'
          };
        case 'week':
          return {
            height: '300px',
            gap: dataLength <= 3 ? '15px' : dataLength <= 5 ? '12px' : '4px',
            barWidth: '100%',
            fontSize: dataLength <= 3 ? '12px' : dataLength <= 5 ? '11px' : '9px',
            maxBars: 20,
            minBarWidth: dataLength <= 3 ? '80px' : dataLength <= 5 ? '60px' : '30px'
          };
        case 'year':
          return {
            height: '350px',
            gap: '12px',
            barWidth: '100%',
            fontSize: '12px',
            maxBars: 10,
            minBarWidth: '50px'
          };
        default: // month
          return {
            height: '300px',
            gap: dataLength <= 3 ? '15px' : dataLength <= 6 ? '12px' : '8px',
            barWidth: '100%',
            fontSize: dataLength <= 3 ? '12px' : dataLength <= 6 ? '11px' : '10px',
            maxBars: 15,
            minBarWidth: dataLength <= 3 ? '80px' : dataLength <= 6 ? '50px' : '40px'
          };
      }
    };
    
    const config = getChartConfig();
    const displayData = data.slice(0, config.maxBars);
    
    // Определяем, нужна ли прокрутка
    const needsScroll = period === 'day' && displayData.length > 15;
    const totalWidth = displayData.length * (parseInt(config.minBarWidth) + parseInt(config.gap));
    
    // Функция для получения цвета с чередованием оттенков
    const getBarColor = (index, isHovered) => {
      const baseColors = {
        conversion_rate: ['#3b82f6', '#1d4ed8', '#1e40af', '#1e3a8a'],
        revenue: ['#10b981', '#059669', '#047857', '#065f46'],
        new_referrals: ['#f59e0b', '#d97706', '#b45309', '#92400e']
      };
      
      const colors = baseColors[dataKey] || baseColors.conversion_rate;
      const colorIndex = index % colors.length;
      const baseColor = colors[colorIndex];
      
      return isHovered ? baseColor : baseColor;
    };
    
    // Функция для получения ширины бара в зависимости от количества данных
    const getBarWidth = (dataLength) => {
      if (dataLength === 1) return '50%';
      if (dataLength === 2) return '60%';
      if (dataLength === 3) return '70%';
      if (dataLength <= 5) return '80%';
      if (dataLength <= 7) return '85%';
      if (dataLength <= 10) return '90%';
      if (dataLength <= 15) return '85%';
      if (dataLength <= 20) return '80%';
      return '70%';
    };
    
    // Функция для определения, нужно ли растягивать бары
    const shouldStretchBars = (dataLength) => {
      return dataLength <= 20;
    };
    
    return (
      <div style={{
        backgroundColor: '#1f2937',
        borderRadius: '12px',
        padding: '24px',
        border: '1px solid #374151'
      }}>
        <h3 style={{ color: 'white', fontSize: '18px', fontWeight: '600', marginBottom: '20px' }}>{title}</h3>
        <div style={{ 
          height: config.height, 
          display: 'flex', 
          alignItems: 'end', 
          gap: config.gap, 
          position: 'relative',
          overflowX: needsScroll ? 'auto' : 'visible',
          justifyContent: displayData.length <= 20 ? (displayData.length <= 3 ? 'center' : 'space-between') : 'flex-start',
          paddingRight: needsScroll ? '10px' : '0',
          paddingLeft: '10px',
          paddingRight: '10px',
          background: 'linear-gradient(180deg, transparent 0%, rgba(59, 130, 246, 0.02) 100%)',
          borderRadius: '8px',
          border: '1px solid rgba(59, 130, 246, 0.1)',
          width: '100%'
        }}>
          {displayData.map((item, index) => {
            const value = Number(item[dataKey] || 0);
            const height = maxValue > 0 ? (value / maxValue) * 200 : 2;
            const periodLabel = item.month || item.period || `${index + 1}`;
            const barColor = getBarColor(index, hoveredIndex === index);
            const barWidth = getBarWidth(displayData.length);
            
            return (
              <div 
                key={index} 
                style={{ 
                  display: 'flex', 
                  flexDirection: 'column', 
                  alignItems: 'center', 
                  flex: needsScroll ? '0 0 auto' : (shouldStretchBars(displayData.length) ? '1' : '0 0 auto'), 
                  position: 'relative',
                  minWidth: needsScroll ? config.minBarWidth : (shouldStretchBars(displayData.length) ? '0' : 'auto'),
                  maxWidth: displayData.length <= 5 ? '120px' : 'none',
                  width: shouldStretchBars(displayData.length) ? '100%' : 'auto',
                  animation: `fadeInUp 0.6s ease-out ${index * 0.1}s both`
                }}
              >
                <div 
                  style={{ 
                    width: barWidth,
                    height: `${height}px`,
                    minHeight: '2px',
                    background: `linear-gradient(135deg, ${barColor} 0%, ${barColor}CC 50%, ${barColor}99 100%)`,
                    borderRadius: period === 'day' ? '2px' : '4px',
                    marginBottom: '8px',
                    cursor: 'pointer',
                    transition: 'all 0.2s ease',
                    transform: hoveredIndex === index ? 'scale(1.05)' : 'scale(1)',
                    boxShadow: hoveredIndex === index ? `0 4px 12px ${barColor}40` : `0 2px 4px ${barColor}20`,
                    opacity: hoveredIndex !== null && hoveredIndex !== index ? 0.7 : 1,
                    border: `1px solid ${barColor}30`,
                    position: 'relative',
                    overflow: 'hidden'
                  }}
                  onMouseEnter={() => setHoveredIndex(index)}
                  onMouseLeave={() => setHoveredIndex(null)}
                >
                  {/* Эффект блика */}
                  <div style={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    height: '30%',
                    background: 'linear-gradient(180deg, rgba(255,255,255,0.2) 0%, transparent 100%)',
                    borderRadius: period === 'day' ? '2px 2px 0 0' : '4px 4px 0 0'
                  }} />
                </div>
                <small style={{ 
                  color: hoveredIndex === index ? '#ffffff' : '#9ca3af', 
                  fontSize: config.fontSize, 
                  textAlign: 'center',
                  writingMode: (period === 'day' && needsScroll) ? 'vertical-rl' : 'horizontal-tb',
                  transform: (period === 'day' && needsScroll) ? 'rotate(180deg)' : 'none',
                  wordBreak: 'break-word',
                  maxWidth: '100%',
                  overflow: 'hidden',
                  textOverflow: 'ellipsis',
                  fontWeight: hoveredIndex === index ? '600' : '400',
                  transition: 'all 0.2s ease'
                }}>
                  {period === 'day' ? (needsScroll ? periodLabel.split('-')[2] : periodLabel) : periodLabel}
                </small>
              
              {/* Invisible bridge area to prevent tooltip from disappearing */}
              {hoveredIndex === index && (
                <div 
                  style={{
                    position: 'absolute',
                    top: '-20px',
                    left: '50%',
                    transform: 'translateX(-50%)',
                    width: '100%',
                    height: '40px',
                    zIndex: 999
                  }}
                  onMouseEnter={() => setHoveredIndex(index)}
                  onMouseLeave={() => setHoveredIndex(null)}
                />
              )}
              
              {/* Tooltip */}
              {hoveredIndex === index && (
                <div 
                  style={{
                    position: 'absolute',
                    top: '-60px',
                    left: '50%',
                    transform: 'translateX(-50%)',
                    backgroundColor: '#1f2937',
                    border: '1px solid #374151',
                    borderRadius: '8px',
                    padding: '12px 16px',
                    boxShadow: '0 10px 25px rgba(0, 0, 0, 0.3)',
                    zIndex: 1000,
                    minWidth: '120px',
                    textAlign: 'center'
                  }}
                  onMouseEnter={() => setHoveredIndex(index)}
                  onMouseLeave={() => setHoveredIndex(null)}
                >
                  <div style={{ color: 'white', fontSize: '14px', fontWeight: '600', marginBottom: '4px' }}>
                    {item.month}
                  </div>
                  <div style={{ color: '#60a5fa', fontSize: '12px' }}>
                    {dataKey === 'revenue' ? 'Выручка:' : dataKey === 'conversion_rate' ? 'Конверсия:' : dataKey === 'new_referrals' ? 'Рефералов:' : 'Значение:'} {
                      dataKey === 'conversion_rate' ? `${Number(item[dataKey]).toFixed(1)}%` : 
                      dataKey === 'revenue' ? formatCurrency(Number(item[dataKey])) : 
                      dataKey === 'new_referrals' ? Number(item[dataKey]) : 
                      formatCurrency(Number(item[dataKey]))
                    }
                  </div>
                  {dataKey === 'revenue' && item.new_referrals && (
                    <div style={{ color: '#9ca3af', fontSize: '11px', marginTop: '4px' }}>
                      Рефералов: {item.new_referrals}
                    </div>
                  )}
                </div>
              )}
            </div>
            );
          })}
        </div>
      </div>
    );
  };

  if (loading) {
    return (
      <div style={{
        minHeight: '100vh',
        backgroundColor: '#111827',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}>
        <div style={{ textAlign: 'center' }}>
          <div style={{
            width: '48px',
            height: '48px',
            border: '4px solid #374151',
            borderTop: '4px solid #2563eb',
            borderRadius: '50%',
            animation: 'spin 1s linear infinite',
            margin: '0 auto 16px'
          }} />
          <p style={{ color: 'white', fontSize: '18px' }}>Загрузка аналитики...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div style={{
        minHeight: '100vh',
        backgroundColor: '#111827',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}>
        <div style={{ textAlign: 'center' }}>
          <p style={{ color: '#ef4444', fontSize: '18px', marginBottom: '16px' }}>{error}</p>
          <button
            onClick={fetchDashboardData}
            style={{
              backgroundColor: '#2563eb',
              color: 'white',
              padding: '12px 24px',
              borderRadius: '8px',
              border: 'none',
              cursor: 'pointer',
              fontWeight: '500'
            }}
          >
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  if (!dashboardData) {
    return (
      <div style={{
        minHeight: '100vh',
        backgroundColor: '#111827',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
      }}>
        <p style={{ color: '#9ca3af', fontSize: '18px' }}>Нет данных для отображения</p>
      </div>
    );
  }

  return (
    <div style={{ minHeight: '100vh', backgroundColor: '#111827', padding: '24px' }}>
      <style>
        {`
          @keyframes fadeInUp {
            from {
              opacity: 0;
              transform: translateY(20px);
            }
            to {
              opacity: 1;
              transform: translateY(0);
            }
          }
        `}
      </style>
      <div style={{ maxWidth: '1200px', margin: '0 auto' }}>
        {/* Заголовок */}
        <div style={{ marginBottom: '32px' }}>
          <h1 style={{ color: 'white', fontSize: '32px', fontWeight: 'bold', marginBottom: '8px' }}>
            📊 Аналитика реферальной программы
          </h1>
          <p style={{ color: '#9ca3af' }}>Детальная статистика по рефералам и розыгрышам</p>
        </div>
        
        {/* Фильтры */}
        <div style={{ display: 'flex', gap: '16px', marginBottom: '32px' }}>
          <select 
            value={period} 
            onChange={(e) => setPeriod(e.target.value)}
            style={{
              backgroundColor: '#1f2937',
              color: 'white',
              border: '1px solid #374151',
              borderRadius: '8px',
              padding: '8px 12px'
            }}
          >
            <option value="day">День</option>
            <option value="week">Неделя</option>
            <option value="month">Месяц</option>
            <option value="year">Год</option>
          </select>
          
          <select 
            value={limit} 
            onChange={(e) => setLimit(Number(e.target.value))}
            style={{
              backgroundColor: '#1f2937',
              color: 'white',
              border: '1px solid #374151',
              borderRadius: '8px',
              padding: '8px 12px'
            }}
          >
            <option value={3}>3 периода</option>
            <option value={6}>6 периодов</option>
            <option value={12}>12 периодов</option>
          </select>
        </div>

        {/* Табы */}
        <div style={{ marginBottom: '24px' }}>
          <div style={{ display: 'flex', gap: '4px', backgroundColor: '#1f2937', borderRadius: '8px', padding: '4px' }}>
            {[
              { key: 'overview', label: 'Обзор' },
              { key: 'conversion', label: 'Конверсия' },
              { key: 'referrers', label: 'Топ рефереры' },
              { key: 'lottery', label: 'Лотерея' }
            ].map(tab => (
              <button
                key={tab.key}
                onClick={() => setActiveTab(tab.key)}
                style={{
                  padding: '8px 16px',
                  borderRadius: '6px',
                  border: 'none',
                  backgroundColor: activeTab === tab.key ? '#2563eb' : 'transparent',
                  color: activeTab === tab.key ? 'white' : '#9ca3af',
                  cursor: 'pointer',
                  fontWeight: '500'
                }}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        {/* Контент табов */}
        {activeTab === 'overview' && (
          <div>
            {/* Общая статистика */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '20px', marginBottom: '32px' }}>
              <StatCard
                title="Всего реферальных кодов"
                value={dashboardData.overall_stats.total_referral_codes}
                icon={<Users size={24} />}
                color="#2563eb"
              />
              <StatCard
                title="Всего рефералов"
                value={dashboardData.overall_stats.total_referrals}
                icon={<TrendingUp size={24} />}
                color="#10b981"
              />
              <StatCard
                title="Выручка от рефералов"
                value={formatCurrency(dashboardData.overall_stats.total_referral_revenue)}
                icon={<DollarSign size={24} />}
                color="#ef4444"
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '20px' }}>
              <StatCard
                title="Лотерейных билетов"
                value={dashboardData.overall_stats.total_lottery_tickets}
                icon={<Trophy size={24} />}
                color="#8b5cf6"
              />
              <StatCard
                title="Бонусных счетов"
                value={dashboardData.overall_stats.total_bonus_accounts}
                icon={<Gift size={24} />}
                color="#06b6d4"
              />
              <StatCard
                title="Рефералов с платежами"
                value={dashboardData.overall_stats.referrals_with_payments}
                icon={<DollarSign size={24} />}
                color="#84cc16"
              />
              <div style={{
                backgroundColor: '#1f2937',
                borderRadius: '12px',
                padding: '24px',
                border: '1px solid #374151'
              }}>
                <p style={{ color: '#9ca3af', fontSize: '14px', marginBottom: '12px' }}>Активность кодов</p>
                <div style={{
                  width: '100%',
                  height: '8px',
                  backgroundColor: '#374151',
                  borderRadius: '4px',
                  marginBottom: '8px'
                }}>
                  <div style={{
                    width: `${dashboardData.activity_stats.activity_rate}%`,
                    height: '100%',
                    backgroundColor: '#2563eb',
                    borderRadius: '4px'
                  }} />
                </div>
                <p style={{ color: 'white', fontSize: '18px', fontWeight: 'bold', marginBottom: '4px' }}>
                  {dashboardData.activity_stats.activity_rate}%
                </p>
                <p style={{ color: '#9ca3af', fontSize: '12px' }}>
                  {dashboardData.activity_stats.active_codes_with_referrals} из {dashboardData.activity_stats.total_active_codes} активных кодов
                </p>
              </div>
            </div>

            {/* Графики по периодам */}
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(500px, 1fr))', gap: '24px', marginTop: '32px' }}>
              <SimpleChart
                data={dashboardData.period_stats.referral_stats}
                dataKey="new_referrals"
                title={`Новые рефералы по ${period === 'day' ? 'дням' : period === 'week' ? 'неделям' : period === 'year' ? 'годам' : 'месяцам'}`}
                period={period}
              />
              <SimpleChart
                data={dashboardData.period_stats.payment_stats}
                dataKey="revenue"
                title={`Выручка от рефералов по ${period === 'day' ? 'дням' : period === 'week' ? 'неделям' : period === 'year' ? 'годам' : 'месяцам'} (руб.)`}
                period={period}
              />
            </div>
          </div>
        )}

        {activeTab === 'conversion' && (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(500px, 1fr))', gap: '24px' }}>
            <SimpleChart
              data={dashboardData.conversion_stats}
              dataKey="conversion_rate"
              title={`Конверсия рефералов в платежи по ${period === 'day' ? 'дням' : period === 'week' ? 'неделям' : period === 'year' ? 'годам' : 'месяцам'} (%)`}
              period={period}
            />
            <SimpleChart
              data={dashboardData.conversion_stats}
              dataKey="revenue"
              title={`Выручка от рефералов по ${period === 'day' ? 'дням' : period === 'week' ? 'неделям' : period === 'year' ? 'годам' : 'месяцам'} (руб.)`}
              period={period}
            />
          </div>
        )}

        {activeTab === 'referrers' && (
          <div style={{
            backgroundColor: '#1f2937',
            borderRadius: '12px',
            padding: '24px',
            border: '1px solid #374151'
          }}>
            <h3 style={{ color: 'white', fontSize: '20px', fontWeight: '600', marginBottom: '20px' }}>
              Топ рефереры
            </h3>
            <div style={{ overflowX: 'auto' }}>
              <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                  <tr style={{ borderBottom: '1px solid #374151' }}>
                    <th style={{ color: '#9ca3af', padding: '12px', textAlign: 'left' }}>Реферальный код</th>
                    <th style={{ color: '#9ca3af', padding: '12px', textAlign: 'left' }}>Chat ID</th>
                    <th style={{ color: '#9ca3af', padding: '12px', textAlign: 'left' }}>Рефералов</th>
                    <th style={{ color: '#9ca3af', padding: '12px', textAlign: 'left' }}>Выручка</th>
                    <th style={{ color: '#9ca3af', padding: '12px', textAlign: 'left' }}>Дата создания</th>
                  </tr>
                </thead>
                <tbody>
                  {dashboardData.top_referrers.map((referrer) => (
                    <tr key={referrer.referral_code} style={{ borderBottom: '1px solid #374151' }}>
                      <td style={{ color: 'white', padding: '12px' }}>
                        <span style={{
                          backgroundColor: '#2563eb',
                          color: 'white',
                          padding: '4px 8px',
                          borderRadius: '4px',
                          fontSize: '12px',
                          fontWeight: '600'
                        }}>
                          {referrer.referral_code}
                        </span>
                      </td>
                      <td style={{ color: 'white', padding: '12px' }}>{referrer.telegraph_chat_id}</td>
                      <td style={{ color: 'white', padding: '12px' }}>
                        <span style={{
                          backgroundColor: '#10b981',
                          color: 'white',
                          padding: '4px 8px',
                          borderRadius: '4px',
                          fontSize: '12px',
                          fontWeight: '600'
                        }}>
                          {referrer.referrals_count}
                        </span>
                      </td>
                      <td style={{ color: 'white', padding: '12px' }}>{formatCurrency(referrer.revenue_from_referrals)}</td>
                      <td style={{ color: 'white', padding: '12px' }}>
                        {new Date(referrer.created_at).toLocaleDateString('ru-RU')}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {activeTab === 'lottery' && (
          <div>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '20px', marginBottom: '32px' }}>
              <StatCard
                title="Всего билетов"
                value={dashboardData.lottery_stats.total_tickets}
                icon={<Trophy size={24} />}
                color="#8b5cf6"
              />
              <StatCard
                title="За подписки"
                value={dashboardData.lottery_stats.tickets_by_source.subscription_payment || 0}
                icon={<DollarSign size={24} />}
                color="#2563eb"
              />
              <StatCard
                title="За рефералов"
                value={dashboardData.lottery_stats.tickets_by_source.referral_bonus || 0}
                icon={<Users size={24} />}
                color="#10b981"
              />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))', gap: '24px' }}>
              <div style={{
                backgroundColor: '#1f2937',
                borderRadius: '12px',
                padding: '24px',
                border: '1px solid #374151'
              }}>
                <h3 style={{ color: 'white', fontSize: '18px', fontWeight: '600', marginBottom: '20px' }}>
                  Распределение билетов по источникам
                </h3>
                <div style={{ display: 'flex', justifyContent: 'space-around', alignItems: 'center', height: '200px' }}>
                  <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: '48px', fontWeight: 'bold', color: '#2563eb', marginBottom: '8px' }}>
                      {dashboardData.lottery_stats.tickets_by_source.subscription_payment || 0}
                    </div>
                    <div style={{ color: '#9ca3af' }}>За подписки</div>
                  </div>
                  <div style={{ textAlign: 'center' }}>
                    <div style={{ fontSize: '48px', fontWeight: 'bold', color: '#10b981', marginBottom: '8px' }}>
                      {dashboardData.lottery_stats.tickets_by_source.referral_bonus || 0}
                    </div>
                    <div style={{ color: '#9ca3af' }}>За рефералов</div>
                  </div>
                </div>
              </div>

              <div style={{
                backgroundColor: '#1f2937',
                borderRadius: '12px',
                padding: '24px',
                border: '1px solid #374151'
              }}>
                <h3 style={{ color: 'white', fontSize: '18px', fontWeight: '600', marginBottom: '20px' }}>
                  Топ держатели билетов
                </h3>
                <div style={{ overflowY: 'auto', maxHeight: '300px' }}>
                  <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                      <tr style={{ borderBottom: '1px solid #374151' }}>
                        <th style={{ color: '#9ca3af', padding: '8px', textAlign: 'left' }}>Chat ID</th>
                        <th style={{ color: '#9ca3af', padding: '8px', textAlign: 'left' }}>Билетов</th>
                      </tr>
                    </thead>
                    <tbody>
                      {dashboardData.lottery_stats.top_ticket_holders.map((holder) => (
                        <tr key={holder.subscription_id} style={{ borderBottom: '1px solid #374151' }}>
                          <td style={{ color: 'white', padding: '8px' }}>{holder.telegraph_chat_id}</td>
                          <td style={{ color: 'white', padding: '8px' }}>
                            <span style={{
                              backgroundColor: '#f59e0b',
                              color: 'white',
                              padding: '2px 6px',
                              borderRadius: '4px',
                              fontSize: '12px',
                              fontWeight: '600'
                            }}>
                              {holder.tickets_count}
                            </span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      <style>{`
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      `}</style>
    </div>
  );
}

export default ReferralAnalytics;
