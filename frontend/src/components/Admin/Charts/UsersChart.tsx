import React, { useState, useEffect } from 'react';
import { Card, Row, Col } from 'react-bootstrap';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import api from '../../../api';
import DateRangePicker from './DateRangePicker';
import { useChartsContext } from '../../../contexts/ChartsContext';

interface ChartDataPoint {
    date: string;
    total_users: number;
    active_users: number;
    online_users: number;
}

const UsersChart: React.FC = () => {
    const [data, setData] = useState<ChartDataPoint[]>([]);
    const { period, startDate, endDate } = useChartsContext();

    const fetchData = async () => {
        try {
            const response = await api.servers.getUsersChartData(period, startDate, endDate);
            setData(response.data.data);
        } catch (error) {
            console.error('Ошибка загрузки данных графика пользователей:', error);
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
                        <h5 className="mb-0">Динамика пользователей</h5>
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
                                    name === 'total_users' ? 'Всего пользователей' :
                                    name === 'active_users' ? 'Активных пользователей' :
                                    'Пользователей онлайн'
                                ]}
                            />
                            <Legend 
                                formatter={(value) => 
                                    value === 'total_users' ? 'Всего пользователей' :
                                    value === 'active_users' ? 'Активных пользователей' :
                                    'Пользователей онлайн'
                                }
                            />
                            <Line 
                                type="monotone" 
                                dataKey="total_users" 
                                stroke="#8884d8" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="active_users" 
                                stroke="#82ca9d" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="online_users" 
                                stroke="#ffc658" 
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

export default UsersChart;
