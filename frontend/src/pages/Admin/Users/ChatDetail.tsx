import React, {useEffect, useState} from 'react';
import {Container, Card, Badge, Alert, Spinner, Table} from 'react-bootstrap';
import {useParams, useNavigate} from 'react-router-dom';
import api from '../../../api';

type ChatData = {
    id: number;
    telegram_id: number;
    username?: string | null;
    first_name?: string | null;
    last_name?: string | null;
    created_at: string;
    tg_link?: string | null;
    is_recovery_processed: boolean;
    status?: string;
};

const statusConfig: Record<string, { label: string; description: string; variant: string }> = {
    'active': {
        label: 'Активная подписка',
        description: 'Подписка активна',
        variant: 'success'
    },
    'passive': {
        label: 'Пассивный чат',
        description: 'Пользователь ни разу не пользовался впн',
        variant: 'warning'
    },
    'blocked_trial': {
        label: 'Истекла пробная подписка',
        description: 'Пользователь воспользовался только пробной подпиской',
        variant: 'danger'
    },
    'blocked_paid': {
        label: 'Истекла платная подписка',
        description: 'Пользователь оплачивал подписку, но не стал продлять дальше',
        variant: 'danger'
    },
    'no_subscription': {
        label: 'Нет подписки',
        description: 'Пользователь зашел в бота, но не создал подписку',
        variant: 'light'
    }
};

function ChatDetail() {
    const { chatId } = useParams<{ chatId: string }>();
    const navigate = useNavigate();
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string | null>(null);
    const [chat, setChat] = useState<ChatData | null>(null);

    useEffect(() => {
        const fetchChat = async () => {
            if (!chatId) {
                setError('ID чата не указан');
                setLoading(false);
                return;
            }

            try {
                setLoading(true);
                const response = await api.chats.getChat(Number(chatId));
                setChat(response.data.data);
                setError(null);
            } catch (err: any) {
                console.error('Ошибка загрузки данных чата:', err);
                setError(err.response?.data?.message || 'Не удалось загрузить данные чата');
            } finally {
                setLoading(false);
            }
        };

        fetchChat();
    }, [chatId]);

    if (loading) {
        return (
            <Container className="mt-5 text-center">
                <Spinner animation="border" role="status">
                    <span className="visually-hidden">Загрузка...</span>
                </Spinner>
            </Container>
        );
    }

    if (error) {
        return (
            <Container className="mt-3">
                <Alert variant="danger">
                    <Alert.Heading>Ошибка</Alert.Heading>
                    <p>{error}</p>
                    <button className="btn btn-outline-danger" onClick={() => navigate('/admin/users/passive')}>
                        Вернуться к списку пользователей
                    </button>
                </Alert>
            </Container>
        );
    }

    if (!chat) {
        return (
            <Container className="mt-3">
                <Alert variant="warning">Чат не найден</Alert>
            </Container>
        );
    }

    const statusInfo = chat.status ? statusConfig[chat.status] : null;

    return (
        <>
            <title>Информация о чате #{chat.telegram_id}</title>
            <Container className="mt-3">
                <div className="mb-3">
                    <button className="btn btn-outline-secondary btn-sm" onClick={() => navigate(-1)}>
                        ← Назад
                    </button>
                </div>

                <h2 className="h2 fw-bold text-dark mb-2">Информация о чате</h2>
                <p className="text-muted mb-3">Telegram ID: {chat.telegram_id}</p>

                {/* Основная информация */}
                <Card className="border-0 rounded-3 mb-3">
                    <Card.Body>
                        <h3 className="h5 fw-semibold mb-3">Основная информация</h3>
                        <Table borderless className="mb-0">
                            <tbody>
                            <tr>
                                <td className="text-muted" style={{width: '200px'}}>Telegram ID:</td>
                                <td className="fw-medium">{chat.telegram_id}</td>
                            </tr>
                            <tr>
                                <td className="text-muted">Никнейм:</td>
                                <td className="fw-medium">{chat.username ? `@${chat.username}` : '-'}</td>
                            </tr>
                            <tr>
                                <td className="text-muted">Имя:</td>
                                <td className="fw-medium">{chat.first_name || '-'}</td>
                            </tr>
                            <tr>
                                <td className="text-muted">Фамилия:</td>
                                <td className="fw-medium">{chat.last_name || '-'}</td>
                            </tr>
                            <tr>
                                <td className="text-muted">Дата регистрации:</td>
                                <td className="fw-medium">{new Date(chat.created_at).toLocaleString('ru-RU')}</td>
                            </tr>
                            <tr>
                                <td className="text-muted">Обработан:</td>
                                <td>
                                    <Badge bg={chat.is_recovery_processed ? 'success' : 'secondary'}>
                                        {chat.is_recovery_processed ? 'Да' : 'Нет'}
                                    </Badge>
                                </td>
                            </tr>
                            {chat.tg_link && (
                                <tr>
                                    <td className="text-muted">Telegram:</td>
                                    <td>
                                        <a
                                            href={chat.tg_link}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="btn btn-sm btn-outline-primary"
                                        >
                                            Открыть в Telegram
                                        </a>
                                    </td>
                                </tr>
                            )}
                            </tbody>
                        </Table>
                    </Card.Body>
                </Card>

                {/* Текущий статус */}
                {statusInfo && (
                    <Card className="border-0 rounded-3 mb-3">
                        <Card.Body>
                            <h3 className="h5 fw-semibold mb-3">Текущий статус</h3>
                            <Alert variant={statusInfo.variant} className="mb-0">
                                <div className="d-flex align-items-center">
                                    <div className="flex-grow-1">
                                        <h5 className="mb-1">{statusInfo.label}</h5>
                                        <p className="mb-0 small">{statusInfo.description}</p>
                                    </div>
                                </div>
                            </Alert>
                        </Card.Body>
                    </Card>
                )}
            </Container>
        </>
    );
}

export default ChatDetail;

