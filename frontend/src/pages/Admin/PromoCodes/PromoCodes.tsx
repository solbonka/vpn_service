import React, { useState, useEffect } from 'react';
import { Container, Card, Button, Table, Badge, Modal, Form, Alert, Spinner } from 'react-bootstrap';
import { Plus, Edit, Trash2, Tag, Percent, Users } from 'lucide-react';
import api from '../../../api';

interface Duration {
  id: number;
  name: string;
  days: number;
  discount_percentage: number;
}

interface PromoCode {
  id: number;
  code: string;
  discount_percent: number;
  is_active: boolean;
  usage_limit: number | null;
  used_count: number;
  expires_at: string | null;
  is_valid: boolean;
  durations: Duration[];
  applicable_durations: string;
  created_at: string;
  updated_at: string;
}

const PromoCodes: React.FC = () => {
  const [promoCodes, setPromoCodes] = useState<PromoCode[]>([]);
  const [availableDurations, setAvailableDurations] = useState<Duration[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [showModal, setShowModal] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [editingPromoCode, setEditingPromoCode] = useState<PromoCode | null>(null);
  const [submitting, setSubmitting] = useState(false);
  const [formData, setFormData] = useState({
    code: '',
    discount_percent: '',
    is_active: true,
    usage_limit: '',
    duration_ids: [] as number[]
  });

  useEffect(() => {
    loadPromoCodes();
    loadAvailableDurations();
  }, []);

  const loadAvailableDurations = async () => {
    try {
      const response = await api.promoCodes.getAvailableDurations();
      if (response.data.success) {
        setAvailableDurations(response.data.data);
      }
    } catch (err: any) {
      console.error('Error loading available durations:', err);
    }
  };

  const loadPromoCodes = async () => {
    try {
      setLoading(true);
      setError(null);
      const response = await api.promoCodes.getPromoCodes();

      if (response.data.success) {
        setPromoCodes(response.data.data);
      } else {
        setError('Ошибка загрузки промокодов');
      }
    } catch (err: any) {
      console.error('Error loading promo codes:', err);
      setError('Ошибка загрузки промокодов');
    } finally {
      setLoading(false);
    }
  };

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    setSubmitting(true);
    setError(null);

    try {
      const data: any = {
        code: formData.code.trim().toUpperCase(),
        discount_percent: parseInt(formData.discount_percent),
        is_active: formData.is_active,
        duration_ids: formData.duration_ids.length > 0 ? formData.duration_ids : null
      };

      if (formData.usage_limit) {
        data.usage_limit = parseInt(formData.usage_limit);
      }

      const response = await api.promoCodes.createPromoCode(data);

      if (response.data.success) {
        setSuccess('Промокод успешно создан');
        setShowModal(false);
        resetForm();
        loadPromoCodes();
      } else {
        setError(response.data.error || 'Ошибка создания промокода');
      }
    } catch (err: any) {
      console.error('Error creating promo code:', err);
      setError(err.response?.data?.error || 'Ошибка создания промокода');
    } finally {
      setSubmitting(false);
    }
  };

  const handleUpdate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPromoCode) return;
    
    setSubmitting(true);
    setError(null);

    try {
      const data: any = {
        code: formData.code.trim().toUpperCase(),
        discount_percent: parseInt(formData.discount_percent),
        is_active: formData.is_active,
        duration_ids: formData.duration_ids.length > 0 ? formData.duration_ids : null
      };

      if (formData.usage_limit) {
        data.usage_limit = parseInt(formData.usage_limit);
      } else {
        data.usage_limit = null;
      }

      const response = await api.promoCodes.updatePromoCode(editingPromoCode.id, data);

      if (response.data.success) {
        setSuccess('Промокод успешно обновлен');
        setShowModal(false);
        setEditingPromoCode(null);
        resetForm();
        loadPromoCodes();
      } else {
        setError(response.data.error || 'Ошибка обновления промокода');
      }
    } catch (err: any) {
      console.error('Error updating promo code:', err);
      setError(err.response?.data?.error || 'Ошибка обновления промокода');
    } finally {
      setSubmitting(false);
    }
  };

  const handleDelete = async (id: number, code: string) => {
    if (!window.confirm(`Вы уверены, что хотите удалить промокод "${code}"?`)) {
      return;
    }

    try {
      const response = await api.promoCodes.deletePromoCode(id);

      if (response.data.success) {
        setSuccess('Промокод успешно удален');
        loadPromoCodes();
      } else {
        setError(response.data.error || 'Ошибка удаления промокода');
      }
    } catch (err: any) {
      console.error('Error deleting promo code:', err);
      setError(err.response?.data?.error || 'Ошибка удаления промокода');
    }
  };

  const resetForm = () => {
    setFormData({
      code: '',
      discount_percent: '',
      is_active: true,
      usage_limit: '',
      duration_ids: []
    });
  };

  const openCreateModal = () => {
    resetForm();
    setModalMode('create');
    setShowModal(true);
  };

  const openEditModal = (promoCode: PromoCode) => {
    setEditingPromoCode(promoCode);
    setModalMode('edit');
    setFormData({
      code: promoCode.code,
      discount_percent: promoCode.discount_percent.toString(),
      is_active: promoCode.is_active,
      usage_limit: promoCode.usage_limit?.toString() || '',
      duration_ids: promoCode.durations.map(d => d.id)
    });
    setShowModal(true);
  };

  const getStatusBadge = (promoCode: PromoCode) => {
    if (!promoCode.is_active) {
      return <Badge bg="secondary">Неактивен</Badge>;
    }
    if (promoCode.usage_limit && promoCode.used_count >= promoCode.usage_limit) {
      return <Badge bg="warning" text="dark">Лимит достигнут</Badge>;
    }
    if (promoCode.is_valid) {
      return <Badge bg="success">Активен</Badge>;
    }
    return <Badge bg="secondary">Неактивен</Badge>;
  };

  if (loading) {
    return (
      <Container className="mt-5 text-center">
        <Spinner animation="border" variant="primary" />
        <p className="mt-3">Загрузка промокодов...</p>
      </Container>
    );
  }

  return (
    <>
      <title>Промокоды</title>
      <Container className="mt-2">
        <div className="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h2 className="h2 fw-bold text-dark mb-1">Промокоды</h2>
            <p className="text-muted mb-0">Управление промокодами и скидками</p>
          </div>
          <Button variant="primary" onClick={openCreateModal}>
            <Plus size={18} className="me-2" />
            Создать промокод
          </Button>
        </div>

        {error && (
          <Alert variant="danger" dismissible onClose={() => setError(null)}>
            {error}
          </Alert>
        )}

        {success && (
          <Alert variant="success" dismissible onClose={() => setSuccess(null)}>
            {success}
          </Alert>
        )}

        <Card className="border-0 rounded-3 shadow-sm">
          <Card.Body className="rounded-3" style={{ backgroundColor: '#f0f4f8' }}>
            {promoCodes.length === 0 ? (
              <div className="text-center py-5">
                <Tag size={64} className="text-muted mb-3" />
                <h4 className="text-muted">Промокодов пока нет</h4>
                <p className="text-muted">Создайте первый промокод для предоставления скидок</p>
                <Button variant="outline-primary" onClick={openCreateModal} className="mt-2">
                  <Plus size={18} className="me-2" />
                  Создать первый промокод
                </Button>
              </div>
            ) : (
              <div className="table-responsive">
                <Table hover className="mb-0" style={{ backgroundColor: 'white' }}>
                  <thead style={{ backgroundColor: '#e9ecef' }}>
                    <tr>
                      <th>Код</th>
                      <th>Скидка</th>
                      <th>Тарифы</th>
                      <th>Статус</th>
                      <th>Использовано</th>
                      <th className="text-end">Действия</th>
                    </tr>
                  </thead>
                  <tbody>
                    {promoCodes.map((promoCode) => (
                      <tr key={promoCode.id}>
                        <td>
                          <div className="d-flex align-items-center">
                            <Tag size={18} className="text-primary me-2" />
                            <strong style={{ fontFamily: 'monospace', fontSize: '16px', letterSpacing: '1px' }}>
                              {promoCode.code}
                            </strong>
                          </div>
                        </td>
                        <td>
                          <Badge bg="primary" className="px-3 py-2">
                            <Percent size={14} className="me-1" />
                            {promoCode.discount_percent}%
                          </Badge>
                        </td>
                        <td>
                          <small className="text-muted">{promoCode.applicable_durations}</small>
                        </td>
                        <td>{getStatusBadge(promoCode)}</td>
                        <td>
                          <div className="d-flex align-items-center">
                            <Users size={16} className="text-muted me-2" />
                            <span>
                              {promoCode.used_count}
                              {promoCode.usage_limit && ` / ${promoCode.usage_limit}`}
                            </span>
                          </div>
                        </td>
                        <td className="text-end">
                          <Button
                            variant="outline-primary"
                            size="sm"
                            className="me-2"
                            onClick={() => openEditModal(promoCode)}
                          >
                            <Edit size={14} />
                          </Button>
                          <Button
                            variant="outline-danger"
                            size="sm"
                            onClick={() => handleDelete(promoCode.id, promoCode.code)}
                          >
                            <Trash2 size={14} />
                          </Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
              </div>
            )}
          </Card.Body>
        </Card>

        {/* Модальное окно */}
        <Modal show={showModal} onHide={() => !submitting && setShowModal(false)} centered>
          <Modal.Header closeButton>
            <Modal.Title>
              {modalMode === 'create' ? (
                <>
                  <Plus size={24} className="me-2 text-primary" />
                  Создать промокод
                </>
              ) : (
                <>
                  <Edit size={24} className="me-2 text-primary" />
                  Редактировать промокод
                </>
              )}
            </Modal.Title>
          </Modal.Header>
          <Form onSubmit={modalMode === 'create' ? handleCreate : handleUpdate}>
            <Modal.Body>
              <Form.Group className="mb-3">
                <Form.Label>Код промокода *</Form.Label>
                <Form.Control
                  type="text"
                  value={formData.code}
                  onChange={(e) => setFormData({ ...formData, code: e.target.value.toUpperCase() })}
                  placeholder="Например: SALE50"
                  required
                  maxLength={20}
                  disabled={submitting}
                  style={{ fontFamily: 'monospace', letterSpacing: '2px', fontSize: '16px' }}
                />
                <Form.Text className="text-muted">
                  Уникальный код, который будут вводить пользователи
                </Form.Text>
              </Form.Group>

              <Form.Group className="mb-3">
                <Form.Label>Процент скидки *</Form.Label>
                <Form.Control
                  type="number"
                  min="1"
                  max="100"
                  value={formData.discount_percent}
                  onChange={(e) => setFormData({ ...formData, discount_percent: e.target.value })}
                  placeholder="50"
                  required
                  disabled={submitting}
                />
                <Form.Text className="text-muted">
                  От 1 до 100 процентов
                </Form.Text>
              </Form.Group>

              <Form.Group className="mb-3">
                <Form.Label>Лимит использований</Form.Label>
                <Form.Control
                  type="number"
                  min="1"
                  value={formData.usage_limit}
                  onChange={(e) => setFormData({ ...formData, usage_limit: e.target.value })}
                  placeholder="Без ограничений"
                  disabled={submitting}
                />
                <Form.Text className="text-muted">
                  Оставьте пустым для безлимитного использования
                </Form.Text>
              </Form.Group>

              <Form.Group className="mb-3">
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <Form.Label className="mb-0">Тарифы (продолжительность)</Form.Label>
                  <Button
                    variant="outline-primary"
                    size="sm"
                    onClick={() => {
                      if (formData.duration_ids.length === availableDurations.length) {
                        setFormData({ ...formData, duration_ids: [] });
                      } else {
                        setFormData({ ...formData, duration_ids: availableDurations.map(d => d.id) });
                      }
                    }}
                    disabled={submitting}
                  >
                    {formData.duration_ids.length === availableDurations.length ? 'Снять все' : 'Выбрать все'}
                  </Button>
                </div>
                <div style={{ 
                  border: '1px solid #dee2e6', 
                  borderRadius: '8px', 
                  padding: '12px',
                  maxHeight: '200px',
                  overflowY: 'auto',
                  backgroundColor: '#f8f9fa'
                }}>
                  {availableDurations.length === 0 ? (
                    <p className="text-muted text-center mb-0">Загрузка тарифов...</p>
                  ) : (
                    availableDurations.map(duration => (
                      <Form.Check
                        key={duration.id}
                        type="checkbox"
                        id={`duration-${duration.id}`}
                        label={`${duration.name} (${duration.days} дней)`}
                        checked={formData.duration_ids.includes(duration.id)}
                        onChange={(e) => {
                          if (e.target.checked) {
                            setFormData({ ...formData, duration_ids: [...formData.duration_ids, duration.id] });
                          } else {
                            setFormData({ ...formData, duration_ids: formData.duration_ids.filter(id => id !== duration.id) });
                          }
                        }}
                        disabled={submitting}
                        className="mb-2"
                      />
                    ))
                  )}
                </div>
                <Form.Text className="text-muted">
                  Выберите тарифы для которых действует промокод. Если не выбрано - действует для всех тарифов
                </Form.Text>
              </Form.Group>

              <Form.Group className="mb-0">
                <Form.Check
                  type="checkbox"
                  id="is_active"
                  label="Промокод активен"
                  checked={formData.is_active}
                  onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                  disabled={submitting}
                />
              </Form.Group>
            </Modal.Body>
            <Modal.Footer>
              <Button
                variant="secondary"
                onClick={() => setShowModal(false)}
                disabled={submitting}
              >
                Отмена
              </Button>
              <Button
                variant="primary"
                type="submit"
                disabled={submitting}
              >
                {submitting ? (
                  <>
                    <Spinner size="sm" className="me-2" />
                    Сохранение...
                  </>
                ) : (
                  <>
                    {modalMode === 'create' ? 'Создать' : 'Сохранить'}
                  </>
                )}
              </Button>
            </Modal.Footer>
          </Form>
        </Modal>
      </Container>
    </>
  );
};

export default PromoCodes;
