import React, { useState, useEffect } from 'react';
import { Card, Row, Col } from 'react-bootstrap';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import api from '../../../api';
import DateRangePicker from './DateRangePicker';
import { useChartsContext } from '../../../contexts/ChartsContext';

interface ChartDataPoint {
    date: string;
    active: number;
    blocked: number;
    new: number;
}

const SubscriptionsChart: React.FC = () => {
    const [data, setData] = useState<ChartDataPoint[]>([]);
    const { period, startDate, endDate } = useChartsContext();

    const fetchData = async () => {
        try {
            const response = await api.subscriptions.getSubscriptionsChartData(period, startDate, endDate);
            setData(response.data.data);
        } catch (error) {
            console.error('Ошибка загрузки данных графика подписок:', error);
        }
    };

    useEffect(() => {
        fetchData();
    }, [period, startDate, endDate]);

    const formatDate = (dateStr: string) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString('ru-RU', { month: 'short', day: 'numeric' });
    };

    const formatNumber = (num: number) => {
        return new Intl.NumberFormat('ru-RU').format(num);
    };


    return (
        <Card className="border-0 rounded-3">
            <Card.Body>
                <Row className="mb-3">
                    <Col>
                        <h5 className="mb-0">Динамика подписок</h5>
                    </Col>
                    <Col xs="auto">
                        <DateRangePicker />
                    </Col>
                </Row>
                
                <div style={{ height: '300px' }}>
                    <ResponsiveContainer width="100%" height="100%">
                        <LineChart data={data}>
                            <CartesianGrid strokeDasharray="3 3" />
                            <XAxis 
                                dataKey="date" 
                                tickFormatter={formatDate}
                                tick={{ fontSize: 12 }}
                            />
                            <YAxis tick={{ fontSize: 12 }} />
                            <Tooltip 
                                labelFormatter={(value) => `Дата: ${formatDate(value)}`}
                                formatter={(value: number, name: string) => [
                                    formatNumber(value), 
                                    name === 'active' ? 'Активных подписок' :
                                    name === 'blocked' ? 'Заблокированных' :
                                    'Новых за день'
                                ]}
                            />
                            <Legend 
                                formatter={(value) => 
                                    value === 'active' ? 'Активных подписок' :
                                    value === 'blocked' ? 'Заблокированных' :
                                    'Новых за день'
                                }
                            />
                            <Line 
                                type="monotone" 
                                dataKey="active" 
                                stroke="#28a745" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="blocked" 
                                stroke="#dc3545" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="new" 
                                stroke="#6f42c1" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                        </LineChart>
                    </ResponsiveContainer>
                </div>
            </Card.Body>
        </Card>
    );
};

export default SubscriptionsChart;
