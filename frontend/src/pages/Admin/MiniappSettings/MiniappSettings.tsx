import React, { useState, useEffect, useRef } from 'react';
import { Container, Card, Button, Alert, Spinner } from 'react-bootstrap';
import { Upload, Trash2, Image as ImageIcon } from 'lucide-react';
import LoadingSpinner from '../../../components/UI/LoadingSpinner';
import api from '../../../api';

interface MiniappSettingsData {
    logo: string | null;
    lottery_prize_image: string | null;
    logo_updated_at: string | null;
    lottery_updated_at: string | null;
}

const MiniappSettings: React.FC = () => {
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [savingLottery, setSavingLottery] = useState(false);
    const [settings, setSettings] = useState<MiniappSettingsData | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [success, setSuccess] = useState<string | null>(null);
    const fileInputRef = useRef<HTMLInputElement>(null);
    const lotteryFileInputRef = useRef<HTMLInputElement>(null);

    const fetchSettings = async () => {
        try {
            setLoading(true);
            const response = await api.miniappSettings.getMiniappSettings();
            setSettings(response.data.data);
            setError(null);
        } catch (err: any) {
            setError('Ошибка загрузки настроек: ' + (err.response?.data?.message || err.message));
        } finally {
            setLoading(false);
        }
    };

    const handleFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file) return;

        // Проверяем тип файла
        if (!file.type.startsWith('image/')) {
            setError('Пожалуйста, выберите изображение');
            return;
        }

        // Проверяем размер файла (максимум 2MB)
        if (file.size > 2 * 1024 * 1024) {
            setError('Размер файла не должен превышать 2MB');
            return;
        }

        // Конвертируем в base64
        const reader = new FileReader();
        reader.onload = async (e) => {
            const base64 = e.target?.result as string;
            await updateLogo(base64);
        };
        reader.readAsDataURL(file);
    };

    const handleLotteryFileSelect = (event: React.ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        if (!file) return;

        // Проверяем тип файла
        if (!file.type.startsWith('image/')) {
            setError('Пожалуйста, выберите изображение');
            return;
        }

        // Проверяем размер файла (максимум 2MB)
        if (file.size > 2 * 1024 * 1024) {
            setError('Размер файла не должен превышать 2MB');
            return;
        }

        // Конвертируем в base64
        const reader = new FileReader();
        reader.onload = async (e) => {
            const base64 = e.target?.result as string;
            await updateLotteryImage(base64);
        };
        reader.readAsDataURL(file);
    };

    const updateLogo = async (logoBase64: string) => {
        try {
            setSaving(true);
            setError(null);
            setSuccess(null);

            const response = await api.miniappSettings.updateMiniappLogo(logoBase64);

            setSettings(response.data.data);
            setSuccess('Логотип успешно обновлен');
        } catch (err: any) {
            setError('Ошибка обновления логотипа: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const updateLotteryImage = async (imageBase64: string) => {
        try {
            setSavingLottery(true);
            setError(null);
            setSuccess(null);

            const response = await api.miniappSettings.updateLotteryImage(imageBase64);

            setSettings(response.data.data);
            setSuccess('Фото приза лотереи успешно обновлено');
        } catch (err: any) {
            setError('Ошибка обновления фото приза: ' + (err.response?.data?.message || err.message));
        } finally {
            setSavingLottery(false);
        }
    };

    const deleteLogo = async () => {
        if (!window.confirm('Вы уверены, что хотите удалить логотип?')) {
            return;
        }

        try {
            setSaving(true);
            setError(null);
            setSuccess(null);

            await api.miniappSettings.deleteMiniappLogo();
            
            if (settings) {
                setSettings({ ...settings, logo: null });
            }
            setSuccess('Логотип успешно удален');
        } catch (err: any) {
            setError('Ошибка удаления логотипа: ' + (err.response?.data?.message || err.message));
        } finally {
            setSaving(false);
        }
    };

    const deleteLotteryImage = async () => {
        if (!window.confirm('Вы уверены, что хотите удалить фото приза лотереи?')) {
            return;
        }

        try {
            setSavingLottery(true);
            setError(null);
            setSuccess(null);

            await api.miniappSettings.deleteLotteryImage();
            
            if (settings) {
                setSettings({ ...settings, lottery_prize_image: null });
            }
            setSuccess('Фото приза лотереи успешно удалено');
        } catch (err: any) {
            setError('Ошибка удаления фото приза: ' + (err.response?.data?.message || err.message));
        } finally {
            setSavingLottery(false);
        }
    };

    useEffect(() => {
        fetchSettings();
    }, []);

    if (loading) {
        return <LoadingSpinner fullscreen />;
    }

    return (
        <>
            <title>Настройки мини-приложения</title>
            <Container className="mt-2">
                <h2 className="h2 fw-bold text-dark mb-2 text-center">Настройки мини-приложения</h2>
                <p className="text-muted mb-4 text-center">Управление логотипом и фото приза лотереи для мини-приложения</p>

                <Card className="border-0 rounded-3">
                    <Card.Body className="rounded-3" style={{ backgroundColor: '#f0f4f8' }}>
                        {error && (
                            <Alert variant="danger" className="mb-4">
                                {error}
                            </Alert>
                        )}

                        {success && (
                            <Alert variant="success" className="mb-4">
                                {success}
                            </Alert>
                        )}

                        <div className="row">
                            <div className="col-md-6">
                                <h4 className="h5 fw-semibold mb-3">Текущий логотип</h4>
                                
                                {settings?.logo ? (
                                    <div className="text-center">
                                        <img
                                            src={settings.logo}
                                            alt="Логотип"
                                            style={{
                                                maxWidth: '200px',
                                                maxHeight: '200px',
                                                objectFit: 'contain',
                                                border: '1px solid #dee2e6',
                                                borderRadius: '8px',
                                                padding: '10px',
                                                backgroundColor: 'white'
                                            }}
                                        />
                                        <div className="mt-3">
                                            <Button
                                                variant="outline-danger"
                                                size="sm"
                                                onClick={deleteLogo}
                                                disabled={saving}
                                            >
                                                {saving ? (
                                                    <Spinner size="sm" className="me-2" />
                                                ) : (
                                                    <Trash2 size={16} className="me-2" />
                                                )}
                                                Удалить логотип
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center text-muted">
                                        <ImageIcon size={48} className="mb-2" />
                                        <p>Логотип не установлен</p>
                                    </div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <h4 className="h5 fw-semibold mb-3">Загрузить новый логотип</h4>
                                
                                <div className="d-grid gap-2">
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept="image/*"
                                        onChange={handleFileSelect}
                                        style={{ display: 'none' }}
                                    />
                                    
                                    <Button
                                        variant="primary"
                                        onClick={() => fileInputRef.current?.click()}
                                        disabled={saving}
                                    >
                                        {saving ? (
                                            <Spinner size="sm" className="me-2" />
                                        ) : (
                                            <Upload size={16} className="me-2" />
                                        )}
                                        Выбрать изображение
                                    </Button>
                                </div>

                                <div className="mt-3">
                                    <small className="text-muted">
                                        <strong>Требования:</strong><br />
                                        • Формат: JPG, PNG, GIF<br />
                                        • Размер: не более 2MB<br />
                                        • Рекомендуемый размер: 200x200px
                                    </small>
                                </div>
                            </div>
                        </div>

                        {/* Секция фото приза лотереи */}
                        <div className="row mt-5">
                            <div className="col-md-6">
                                <h4 className="h5 fw-semibold mb-3">Фото приза лотереи</h4>
                                
                                {settings?.lottery_prize_image ? (
                                    <div className="text-center">
                                        <img
                                            src={settings.lottery_prize_image}
                                            alt="Фото приза лотереи"
                                            style={{
                                                maxWidth: '200px',
                                                maxHeight: '200px',
                                                objectFit: 'contain',
                                                border: '1px solid #dee2e6',
                                                borderRadius: '8px',
                                                padding: '10px',
                                                backgroundColor: 'white'
                                            }}
                                        />
                                        <div className="mt-3">
                                            <Button
                                                variant="outline-danger"
                                                size="sm"
                                                onClick={deleteLotteryImage}
                                                disabled={savingLottery}
                                            >
                                                {savingLottery ? (
                                                    <Spinner size="sm" className="me-2" />
                                                ) : (
                                                    <Trash2 size={16} className="me-2" />
                                                )}
                                                Удалить фото
                                            </Button>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="text-center text-muted">
                                        <ImageIcon size={48} className="mb-2" />
                                        <p>Фото приза не установлено</p>
                                    </div>
                                )}
                            </div>

                            <div className="col-md-6">
                                <h4 className="h5 fw-semibold mb-3">Загрузить фото приза</h4>
                                
                                <div className="d-grid gap-2">
                                    <input
                                        ref={lotteryFileInputRef}
                                        type="file"
                                        accept="image/*"
                                        onChange={handleLotteryFileSelect}
                                        style={{ display: 'none' }}
                                    />
                                    
                                    <Button
                                        variant="primary"
                                        onClick={() => lotteryFileInputRef.current?.click()}
                                        disabled={savingLottery}
                                    >
                                        {savingLottery ? (
                                            <Spinner size="sm" className="me-2" />
                                        ) : (
                                            <Upload size={16} className="me-2" />
                                        )}
                                        Выбрать изображение
                                    </Button>
                                </div>

                                <div className="mt-3">
                                    <small className="text-muted">
                                        <strong>Требования:</strong><br />
                                        • Формат: JPG, PNG, GIF<br />
                                        • Размер: не более 2MB<br />
                                        • Рекомендуемый размер: 300x300px
                                    </small>
                                </div>
                            </div>
                        </div>

                        {settings && (
                            <div className="mt-4 pt-3 border-top">
                                <small className="text-muted">
                                    {settings.logo_updated_at && (
                                        <>Логотип обновлен: {new Date(settings.logo_updated_at).toLocaleString('ru-RU')}<br /></>
                                    )}
                                    {settings.lottery_updated_at && (
                                        <>Фото приза обновлено: {new Date(settings.lottery_updated_at).toLocaleString('ru-RU')}</>
                                    )}
                                </small>
                            </div>
                        )}
                    </Card.Body>
                </Card>
            </Container>
        </>
    );
};

export default MiniappSettings;
