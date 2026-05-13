import React, {useState, useEffect} from 'react';
import {Container, Card, Row, Col} from 'react-bootstrap';
import {TrendingUp, TrendingDown} from 'lucide-react';
import LoadingSpinner from '../../../components/UI/LoadingSpinner';
import api from "../../../api";
import ServetItem from '../../../components/Admin/MainItem/ServerItem';
import SubscriptionItem from '../../../components/Admin/MainItem/SubscriptionItem';
import PaymentItem from '../../../components/Admin/MainItem/PaymentItem';
import { UsersChart, SubscriptionsChart, PaymentsChart } from '../../../components/Admin/Charts';
import { ChartsProvider } from '../../../contexts/ChartsContext';

function Main() {
    const [loading, setLoading] = useState(true);
    const [servers, setServers] = useState<any[]>([]);
    const [subscriptions, setSubscriptions] = useState<any[]>([]);
    const [payments, setPayments] = useState<any[]>([]);

    const formatNumber = (num: number) => {
        return new Intl.NumberFormat('ru-RU').format(num);
    };

    const PercentageIndicator = ({value, isPositive}: { value: number; isPositive: boolean }) => (
        <div className={`d-flex align-items-center mt-1 ${isPositive ? 'text-success' : 'text-danger'}`}>
            {isPositive
                ? <TrendingUp size={12} className="me-1"/>
                : <TrendingDown size={12} className="me-1"/>}
            <small className="fw-medium">
                {isPositive ? '+' : ''}{value}%
            </small>
        </div>
    );

    useEffect(() => {
        let timeout: NodeJS.Timeout;

        const fetchData = () => {
            const promises = [
                api.subscriptions.getSubscriptionsMetrics(),
                api.payments.getPaymentsMetrics(),
                api.marzban.getServersMetrics()
            ];

            Promise.allSettled(promises)
                .then((results) => {
                    const [subsRes, paymentRes, serversRes] = results;

                    if (serversRes.status === "fulfilled") {
                        setServers(serversRes.value.data.data);
                    } else {
                        console.error("Ошибка загрузки метрик серверов:", serversRes.reason);
                    }

                    if (subsRes.status === "fulfilled") {
                        setSubscriptions(subsRes.value.data.data);
                    } else {
                        console.error("Ошибка загрузки метрик подписок:", subsRes.reason);
                    }

                    if (paymentRes.status === "fulfilled") {
                        setPayments(paymentRes.value.data.data);
                    } else {
                        console.error("Ошибка загрузки метрик платежей:", paymentRes.reason);
                    }

                    const allRejected = results.every(r => r.status === "rejected");
                    if (!allRejected) {
                        setLoading(false);
                    }
                })
                .finally(() => {
                    timeout = setTimeout(fetchData, 10000);
                });
        };

        fetchData();

        return () => clearTimeout(timeout);
    }, []);

    if (loading) {
        return <LoadingSpinner fullscreen/>;
    }

    return (
        <>
            <title>Главная</title>
            <Container className="mt-2">
                <h2 className="h2 fw-bold text-dark mb-2 text-center">Дашборд</h2>
                <p className="text-muted mb-4 text-center">Общая статистика</p>

                <Card className="border-0 rounded-3">
                    <Card.Body className="rounded-3" style={{ backgroundColor: '#f0f4f8' }}>
                        {/* Статистика по серверам */}
                        <div className="mb-4">
                            <h3 className="h4 fw-semibold mb-3">Серверы</h3>

                            <ServetItem
                                PercentageIndicator={PercentageIndicator}
                                formatNumber={formatNumber}
                                servers={servers}
                            />
                        </div>

                        {/* Статистика по подпискам */}
                        <div className="mb-4">
                            <SubscriptionItem
                                PercentageIndicator={PercentageIndicator}
                                formatNumber={formatNumber}
                                subscriptions={subscriptions}
                            />
                        </div>

                        {/* Статистика по платежам */}
                        <div>
                            <h3 className="h4 fw-semibold mb-3">Платежи</h3>

                            <PaymentItem
                                PercentageIndicator={PercentageIndicator}
                                payments={payments}
                            />
                        </div>
                    </Card.Body>
                </Card>

                {/* Графики */}
                <div className="mt-4">
                    <h3 className="h4 fw-semibold mb-4 text-center">Аналитика</h3>
                    
                    <ChartsProvider>
                        <Row className="g-4">
                            <Col lg={12}>
                                <UsersChart />
                            </Col>
                            <Col lg={12}>
                                <SubscriptionsChart />
                            </Col>
                            <Col lg={12}>
                                <PaymentsChart />
                            </Col>
                        </Row>
                    </ChartsProvider>
                </div>
            </Container>
        </>
    );
}

export default Main;