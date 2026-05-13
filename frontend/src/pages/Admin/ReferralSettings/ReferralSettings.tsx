import React, { useState, useEffect } from 'react';
import { Plus, Edit, Trash2, Check, X, Gift, Calendar, Ticket } from 'lucide-react';
import { useAuth } from '../../../hooks/useAuth';
import api from '../../../api';

interface BonusType {
  id: number;
  name: string;
  type: string;
  type_label: string;
  amount: number;
  description: string;
  is_active: boolean;
  formatted_amount: string;
  created_at: string;
  updated_at: string;
}

interface AvailableType {
  value: string;
  label: string;
  description: string;
}

interface ReferralSettingsData {
  bonus_types: BonusType[];
  available_types: AvailableType[];
}

const ReferralSettings: React.FC = () => {
  const { token } = useAuth();
  const [data, setData] = useState<ReferralSettingsData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showEditModal, setShowEditModal] = useState(false);
  const [editingBonusType, setEditingBonusType] = useState<BonusType | null>(null);
  const [formData, setFormData] = useState({
    name: '',
    type: '',
    type_label: '',
    amount: '',
    description: '',
    is_active: false
  });

  useEffect(() => {
    loadData();
  }, []);

  const loadData = async () => {
    try {
      setLoading(true);
      const response = await api.referralSettings.getReferralSettings();

      if (response.data.success) {
        setData(response.data.data);
      } else {
        setError('Ошибка загрузки данных');
      }
    } catch (err: any) {
      console.error('Error loading referral settings:', err);
      setError('Ошибка загрузки настроек реферальной программы');
    } finally {
      setLoading(false);
    }
  };


  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingBonusType) return;

    try {
      const response = await api.referralSettings.updateBonusType(editingBonusType.id, formData);

      if (response.data.success) {
        setShowEditModal(false);
        setEditingBonusType(null);
        resetForm();
        loadData();
      } else {
        setError(response.data.error || 'Ошибка обновления типа бонуса');
      }
    } catch (err: any) {
      console.error('Error updating bonus type:', err);
      setError('Ошибка обновления типа бонуса');
    }
  };


  const handleActivate = async (id: number) => {
    try {
      const response = await api.referralSettings.activateBonusType(id);

      if (response.data.success) {
        loadData();
      } else {
        setError(response.data.error || 'Ошибка активации типа бонуса');
      }
    } catch (err: any) {
      console.error('Error activating bonus type:', err);
      setError('Ошибка активации типа бонуса');
    }
  };

  const resetForm = () => {
    setFormData({
      name: '',
      type: '',
      type_label: '',
      amount: '',
      description: '',
      is_active: false
    });
  };

  const openEditModal = (bonusType: BonusType) => {
    setEditingBonusType(bonusType);
    setFormData({
      name: bonusType.name,
      type: bonusType.type,
      type_label: bonusType.type_label,
      amount: bonusType.amount.toString(),
      description: bonusType.description,
      is_active: bonusType.is_active
    });
    setShowEditModal(true);
  };

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'rubles':
        return <Gift className="w-4 h-4 text-green-500" />;
      case 'days':
        return <Calendar className="w-4 h-4 text-blue-500" />;
      case 'lottery_tickets':
        return <Ticket className="w-4 h-4 text-purple-500" />;
      default:
        return <Gift className="w-4 h-4 text-gray-500" />;
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
          <p className="text-white text-lg">Загрузка настроек...</p>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-900 flex items-center justify-center">
        <div className="text-center">
          <p className="text-red-500 text-lg mb-4">{error}</p>
          <button
            onClick={loadData}
            className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700"
          >
            Попробовать снова
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-900 text-white p-6">
      <div className="max-w-6xl mx-auto">
        {/* Заголовок */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">🎁 Настройки реферальной программы</h1>
          <p className="text-gray-400">Настройка типов бонусов для реферальной программы</p>
        </div>

        {/* Список типов бонусов */}
        <div style={{ backgroundColor: '#1f2937', borderRadius: '8px', padding: '24px' }}>
          <h2 style={{ fontSize: '20px', fontWeight: '600', marginBottom: '16px', color: 'white' }}>Типы бонусов</h2>
          
          {data?.bonus_types.length === 0 ? (
            <div style={{ textAlign: 'center', padding: '32px' }}>
              <p style={{ color: '#9ca3af', marginBottom: '16px' }}>Загрузка типов бонусов...</p>
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: '16px' }}>
              {data?.bonus_types.map((bonusType) => (
                <div
                  key={bonusType.id}
                  style={{
                    padding: '20px',
                    borderRadius: '12px',
                    border: '2px solid',
                    borderColor: bonusType.is_active ? '#10b981' : '#4b5563',
                    backgroundColor: bonusType.is_active ? 'rgba(16, 185, 129, 0.1)' : 'rgba(75, 85, 99, 0.1)',
                    transition: 'all 0.2s ease'
                  }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px', flex: 1 }}>
                      <div style={{
                        padding: '12px',
                        borderRadius: '50%',
                        backgroundColor: bonusType.is_active ? 'rgba(16, 185, 129, 0.2)' : 'rgba(75, 85, 99, 0.2)'
                      }}>
                        {getTypeIcon(bonusType.type)}
                      </div>
                      <div style={{ flex: 1 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '8px' }}>
                          <h3 style={{ fontSize: '18px', fontWeight: 'bold', color: 'white', margin: 0 }}>
                            {bonusType.name}
                          </h3>
                          {bonusType.is_active && (
                            <span style={{
                              padding: '4px 12px',
                              backgroundColor: '#10b981',
                              color: 'white',
                              fontSize: '12px',
                              fontWeight: '600',
                              borderRadius: '20px'
                            }}>
                              АКТИВЕН
                            </span>
                          )}
                        </div>
                        <p style={{ color: '#d1d5db', marginBottom: '4px', margin: 0 }}>{bonusType.type_label}</p>
                        <p style={{ color: '#9ca3af', fontSize: '14px', margin: 0 }}>{bonusType.description}</p>
                      </div>
                    </div>
                    
                    <div style={{ display: 'flex', alignItems: 'center', gap: '16px' }}>
                      <div style={{ textAlign: 'right' }}>
                        <p style={{
                          fontSize: '24px',
                          fontWeight: 'bold',
                          color: '#10b981',
                          marginBottom: '4px',
                          margin: 0
                        }}>
                          {bonusType.formatted_amount}
                        </p>
                        <p style={{ fontSize: '12px', color: '#9ca3af', margin: 0 }}>Бонус за реферал</p>
                      </div>
                      
                      <div style={{ display: 'flex', gap: '12px' }}>
                        {!bonusType.is_active && (
                          <button
                            onClick={() => handleActivate(bonusType.id)}
                            style={{
                              padding: '8px 16px',
                              backgroundColor: '#059669',
                              color: 'white',
                              borderRadius: '8px',
                              border: 'none',
                              cursor: 'pointer',
                              display: 'flex',
                              alignItems: 'center',
                              gap: '8px',
                              fontWeight: '500'
                            }}
                          >
                            <Check style={{ width: '16px', height: '16px' }} />
                            Активировать
                          </button>
                        )}
                        
                        <button
                          onClick={() => openEditModal(bonusType)}
                          style={{
                            padding: '8px 16px',
                            backgroundColor: '#2563eb',
                            color: 'white',
                            borderRadius: '8px',
                            border: 'none',
                            cursor: 'pointer',
                            display: 'flex',
                            alignItems: 'center',
                            gap: '8px',
                            fontWeight: '500'
                          }}
                        >
                          <Edit style={{ width: '16px', height: '16px' }} />
                          Редактировать
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>


        {/* Модальное окно редактирования */}
        {showEditModal && editingBonusType && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            backgroundColor: 'rgba(0, 0, 0, 0.6)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 50,
            padding: '16px'
          }}>
            <div style={{
              backgroundColor: '#1f2937',
              borderRadius: '16px',
              padding: '32px',
              width: '100%',
              maxWidth: '500px',
              boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)'
            }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: '12px', marginBottom: '24px' }}>
                <div style={{
                  padding: '8px',
                  backgroundColor: 'rgba(59, 130, 246, 0.2)',
                  borderRadius: '8px'
                }}>
                  <Edit style={{ width: '24px', height: '24px', color: '#60a5fa' }} />
                </div>
                <h2 style={{ fontSize: '24px', fontWeight: 'bold', color: 'white', margin: 0 }}>
                  Редактировать тип бонуса
                </h2>
              </div>
              
              <form onSubmit={handleUpdate}>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '24px' }}>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '16px' }}>
                    <div>
                      <label style={{
                        display: 'block',
                        fontSize: '14px',
                        fontWeight: '500',
                        color: '#d1d5db',
                        marginBottom: '8px'
                      }}>
                        Название
                      </label>
                      <input
                        type="text"
                        value={formData.name}
                        disabled
                        style={{
                          width: '100%',
                          padding: '12px 16px',
                          backgroundColor: '#374151',
                          border: '1px solid #4b5563',
                          borderRadius: '12px',
                          color: '#9ca3af',
                          cursor: 'not-allowed'
                        }}
                      />
                    </div>
                    
                    <div>
                      <label style={{
                        display: 'block',
                        fontSize: '14px',
                        fontWeight: '500',
                        color: '#d1d5db',
                        marginBottom: '8px'
                      }}>
                        Тип бонуса
                      </label>
                      <input
                        type="text"
                        value={formData.type_label || formData.type}
                        disabled
                        style={{
                          width: '100%',
                          padding: '12px 16px',
                          backgroundColor: '#374151',
                          border: '1px solid #4b5563',
                          borderRadius: '12px',
                          color: '#9ca3af',
                          cursor: 'not-allowed'
                        }}
                      />
                    </div>
                  </div>
                  
                  <div>
                    <label style={{
                      display: 'block',
                      fontSize: '14px',
                      fontWeight: '500',
                      color: '#d1d5db',
                      marginBottom: '8px'
                    }}>
                      Количество бонуса
                    </label>
                    <input
                      type="number"
                      min="1"
                      value={formData.amount}
                      onChange={(e) => setFormData({ ...formData, amount: e.target.value })}
                      style={{
                        width: '100%',
                        padding: '12px 16px',
                        backgroundColor: '#374151',
                        border: '1px solid #4b5563',
                        borderRadius: '12px',
                        color: 'white',
                        outline: 'none'
                      }}
                      required
                    />
                  </div>
                  
                  <div>
                    <label style={{
                      display: 'block',
                      fontSize: '14px',
                      fontWeight: '500',
                      color: '#d1d5db',
                      marginBottom: '8px'
                    }}>
                      Описание
                    </label>
                    <textarea
                      value={formData.description}
                      onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                      style={{
                        width: '100%',
                        padding: '12px 16px',
                        backgroundColor: '#374151',
                        border: '1px solid #4b5563',
                        borderRadius: '12px',
                        color: 'white',
                        outline: 'none',
                        resize: 'none',
                        minHeight: '80px'
                      }}
                      rows={3}
                    />
                  </div>
                  
                  <div style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '12px',
                    padding: '16px',
                    backgroundColor: 'rgba(75, 85, 99, 0.5)',
                    borderRadius: '12px'
                  }}>
                    <input
                      type="checkbox"
                      id="is_active_edit"
                      checked={formData.is_active}
                      onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                      style={{
                        width: '20px',
                        height: '20px',
                        accentColor: '#2563eb'
                      }}
                    />
                    <label htmlFor="is_active_edit" style={{
                      color: 'white',
                      fontWeight: '500',
                      cursor: 'pointer'
                    }}>
                      Сделать активным типом бонуса
                    </label>
                  </div>
                </div>
                
                <div style={{ display: 'flex', gap: '16px', marginTop: '32px' }}>
                  <button
                    type="submit"
                    style={{
                      flex: 1,
                      backgroundColor: '#2563eb',
                      color: 'white',
                      padding: '12px 24px',
                      borderRadius: '12px',
                      border: 'none',
                      cursor: 'pointer',
                      fontWeight: '600',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: '8px'
                    }}
                  >
                    <Check style={{ width: '20px', height: '20px' }} />
                    Сохранить изменения
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setShowEditModal(false);
                      setEditingBonusType(null);
                      resetForm();
                    }}
                    style={{
                      flex: 1,
                      backgroundColor: '#4b5563',
                      color: 'white',
                      padding: '12px 24px',
                      borderRadius: '12px',
                      border: 'none',
                      cursor: 'pointer',
                      fontWeight: '600'
                    }}
                  >
                    Отмена
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </div>
  );
};

export default ReferralSettings;
