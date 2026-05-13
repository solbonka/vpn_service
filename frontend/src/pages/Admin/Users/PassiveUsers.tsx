import React, {useEffect, useState} from 'react';
import {Container, Card, Table, Badge, Nav, Tab} from 'react-bootstrap';
import api from '../../../api';
import LoadingSpinner from '../../../components/UI/LoadingSpinner';

type PassiveUser = {
    id: number;
    telegram_id: number;
    username?: string | null;
    first_name?: string | null;
    last_name?: string | null;
    created_at: string; // ISO
    tg_link?: string | null;
    is_recovery_processed: boolean;
};

type ApiResponse = {
    data: PassiveUser[];
    meta: {
        total: number | number[];
        per_page: number | number[];
        current_page: number | number[];
        last_page: number | number[];
    };
};

// Хелпер для получения значения из метаданных (на случай если приходит массив)
const getMetaValue = (value: number | number[]): number => {
    return Array.isArray(value) ? value[0] : value;
};

type TabType = 'passive' | 'inactive-trial' | 'inactive-paid';

function PassiveUsers() {
    const [activeTab, setActiveTab] = useState<TabType>('passive');
    const [loading, setLoading] = useState<boolean>(true);
    const [users, setUsers] = useState<PassiveUser[]>([]);
    const [page, setPage] = useState<number>(1);
    const [pageTrial, setPageTrial] = useState<number>(1);
    const [pagePaid, setPagePaid] = useState<number>(1);
    const [total, setTotal] = useState<number>(0);
    const [totalTrial, setTotalTrial] = useState<number>(0);
    const [totalPaid, setTotalPaid] = useState<number>(0);
    const [currentPage, setCurrentPage] = useState<number>(1);
    const [currentPageTrial, setCurrentPageTrial] = useState<number>(1);
    const [currentPagePaid, setCurrentPagePaid] = useState<number>(1);
    const [lastPage, setLastPage] = useState<number>(1);
    const [lastPageTrial, setLastPageTrial] = useState<number>(1);
    const [lastPagePaid, setLastPagePaid] = useState<number>(1);

    // Загрузка данных для вкладки "Не подключались"
    useEffect(() => {
        if (activeTab !== 'passive') {
            return;
        }

        const fetchData = async () => {
            setLoading(true);
            try {
                const response = await api.chats.getPassiveUsers(page);
                const data: ApiResponse = response.data;
                setUsers(data.data);
                setTotal(getMetaValue(data.meta.total));
                setCurrentPage(getMetaValue(data.meta.current_page));
                setLastPage(getMetaValue(data.meta.last_page));
            } catch (error) {
                console.error('Ошибка загрузки пассивных пользователей:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [page, activeTab]);

    // Загрузка данных для вкладки "Неактивные — пробный период"
    useEffect(() => {
        if (activeTab !== 'inactive-trial') {
            return;
        }

        const fetchData = async () => {
            setLoading(true);
            try {
                const response = await api.chats.getBlockedUsers(pageTrial, true);
                const data: ApiResponse = response.data;
                setUsers(data.data);
                setTotalTrial(getMetaValue(data.meta.total));
                setCurrentPageTrial(getMetaValue(data.meta.current_page));
                setLastPageTrial(getMetaValue(data.meta.last_page));
            } catch (error) {
                console.error('Ошибка загрузки неактивных пользователей (пробный период):', error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [pageTrial, activeTab]);

    // Загрузка данных для вкладки "Неактивные — платные"
    useEffect(() => {
        if (activeTab !== 'inactive-paid') {
            return;
        }

        const fetchData = async () => {
            setLoading(true);
            try {
                const response = await api.chats.getBlockedUsers(pagePaid, false);
                const data: ApiResponse = response.data;
                setUsers(data.data);
                setTotalPaid(getMetaValue(data.meta.total));
                setCurrentPagePaid(getMetaValue(data.meta.current_page));
                setLastPagePaid(getMetaValue(data.meta.last_page));
            } catch (error) {
                console.error('Ошибка загрузки неактивных пользователей (платные):', error);
            } finally {
                setLoading(false);
            }
        };

        fetchData();
    }, [pagePaid, activeTab]);

    // Сбрасываем страницу при смене вкладки
    useEffect(() => {
        if (activeTab === 'passive') {
            setPage(1);
        } else if (activeTab === 'inactive-trial') {
            setPageTrial(1);
        } else {
            setPagePaid(1);
        }
    }, [activeTab]);

    const getCurrentUsers = (): PassiveUser[] => {
        return users;
    };

    const getCurrentTotal = (): number => {
        if (activeTab === 'passive') return total;
        if (activeTab === 'inactive-trial') return totalTrial;
        return totalPaid;
    };

    const getCurrentPageInfo = () => {
        if (activeTab === 'passive') {
            return {
                current: currentPage,
                last: lastPage,
                onPrev: () => setPage(p => Math.max(1, p - 1)),
                onNext: () => setPage(p => Math.min(lastPage, p + 1)),
            };
        } else if (activeTab === 'inactive-trial') {
            return {
                current: currentPageTrial,
                last: lastPageTrial,
                onPrev: () => setPageTrial(p => Math.max(1, p - 1)),
                onNext: () => setPageTrial(p => Math.min(lastPageTrial, p + 1)),
            };
        } else {
            return {
                current: currentPagePaid,
                last: lastPagePaid,
                onPrev: () => setPagePaid(p => Math.max(1, p - 1)),
                onNext: () => setPagePaid(p => Math.min(lastPagePaid, p + 1)),
            };
        }
    };

    const getTabTitle = (): string => {
        if (activeTab === 'passive') return 'Не подключались';
        if (activeTab === 'inactive-trial') return 'Неактивные — пробный период';
        return 'Неактивные — платные';
    };

    const handleToggleRecoveryProcessed = async (user: PassiveUser) => {
        try {
            const newValue = !user.is_recovery_processed;
            await api.chats.updateChat(user.id, { is_recovery_processed: newValue });
            
            // Обновляем состояние локально
            setUsers(users.map(u => 
                u.id === user.id 
                    ? { ...u, is_recovery_processed: newValue }
                    : u
            ));
        } catch (error) {
            console.error('Ошибка обновления флага восстановления:', error);
            alert('Не удалось обновить флаг восстановления');
        }
    };

    const currentUsers = getCurrentUsers();
    const currentTotal = getCurrentTotal();

    if (loading) {
        return <LoadingSpinner fullscreen/>;
    }

    return (
        <>
            <title>Пользователи</title>
            <Container className="mt-3">
                <h2 className="h2 fw-bold text-dark mb-2">Пользователи</h2>
                <p className="text-muted mb-3">Управление пользователями</p>

                <Card className="border-0 rounded-3">
                    <Card.Body>
                        <Tab.Container activeKey={activeTab} onSelect={(k) => setActiveTab(k as TabType)}>
                            <Nav variant="tabs" className="mb-3">
                                <Nav.Item>
                                    <Nav.Link eventKey="passive">Не подключались</Nav.Link>
                                </Nav.Item>
                                <Nav.Item>
                                    <Nav.Link eventKey="inactive-trial">Неактивные — пробный период</Nav.Link>
                                </Nav.Item>
                                <Nav.Item>
                                    <Nav.Link eventKey="inactive-paid">Неактивные — платные</Nav.Link>
                                </Nav.Item>
                            </Nav>

                            <Tab.Content>
                                <Tab.Pane eventKey={activeTab}>
                                    <div className="d-flex justify-content-between align-items-center mb-3">
                                        <h3 className="h5 fw-semibold mb-0">{getTabTitle()}</h3>
                                        <Badge bg="warning" text="dark">{currentTotal} всего</Badge>
                                    </div>

                                    <Table responsive hover className="align-middle">
                                        <thead>
                                        <tr>
                                            <th>Telegram ID</th>
                                            <th>Ник</th>
                                            <th>Имя</th>
                                            <th>Фамилия</th>
                                            <th>Создан</th>
                                            <th>Обработан</th>
                                            <th>Открыть в TG</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {currentUsers.length === 0 ? (
                                            <tr>
                                                <td colSpan={7} className="text-center text-muted py-4">
                                                    Нет данных
                                                </td>
                                            </tr>
                                        ) : (
                                            currentUsers.map((u) => (
                                                <tr key={u.telegram_id}>
                                                    <td>{u.telegram_id}</td>
                                                    <td>{u.username || '-'}</td>
                                                    <td>{u.first_name || '-'}</td>
                                                    <td>{u.last_name || '-'}</td>
                                                    <td>{new Date(u.created_at).toLocaleString('ru-RU')}</td>
                                                    <td>
                                                        <div className="form-check form-switch">
                                                            <input
                                                                className="form-check-input"
                                                                type="checkbox"
                                                                checked={u.is_recovery_processed}
                                                                onChange={() => handleToggleRecoveryProcessed(u)}
                                                                id={`recovery-${u.id}`}
                                                            />
                                                            <label 
                                                                className="form-check-label" 
                                                                htmlFor={`recovery-${u.id}`}
                                                            >
                                                                {u.is_recovery_processed ? 'Да' : 'Нет'}
                                                            </label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        {u.tg_link ? (
                                                            <a
                                                                href={u.tg_link}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="btn btn-sm btn-outline-primary"
                                                            >
                                                                Открыть в TG
                                                            </a>
                                                        ) : (
                                                            '-'
                                                        )}
                                                    </td>
                                                </tr>
                                            ))
                                        )}
                                        </tbody>
                                    </Table>

                                    {(() => {
                                        const pageInfo = getCurrentPageInfo();
                                        return (
                                            <div className="d-flex justify-content-between align-items-center mt-2">
                                                <div className="text-muted small">
                                                    Страница {pageInfo.current} из {pageInfo.last}
                                                </div>
                                                <div className="btn-group">
                                                    <button
                                                        className="btn btn-sm btn-outline-secondary"
                                                        onClick={pageInfo.onPrev}
                                                        disabled={pageInfo.current <= 1}
                                                    >
                                                        Назад
                                                    </button>
                                                    <button
                                                        className="btn btn-sm btn-outline-secondary"
                                                        onClick={pageInfo.onNext}
                                                        disabled={pageInfo.current >= pageInfo.last}
                                                    >
                                                        Вперед
                                                    </button>
                                                </div>
                                            </div>
                                        );
                                    })()}
                                </Tab.Pane>
                            </Tab.Content>
                        </Tab.Container>
                    </Card.Body>
                </Card>
            </Container>
        </>
    );
}

export default PassiveUsers;


