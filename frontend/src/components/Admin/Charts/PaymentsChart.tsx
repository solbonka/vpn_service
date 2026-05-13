import React, { useState, useEffect } from 'react';
import { Card, Row, Col } from 'react-bootstrap';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import api from '../../../api';
import DateRangePicker from './DateRangePicker';
import { useChartsContext } from '../../../contexts/ChartsContext';

interface ChartDataPoint {
    date: string;
    total_amount: number;
    count: number;
    average_check: number;
}

const PaymentsChart: React.FC = () => {
    const [data, setData] = useState<ChartDataPoint[]>([]);
    const { period, startDate, endDate } = useChartsContext();

    const fetchData = async () => {
        try {
            const response = await api.payments.getPaymentsChartData(period, startDate, endDate);
            setData(response.data.data);
        } catch (error) {
            console.error('Ошибка загрузки данных графика платежей:', error);
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

    const formatCurrency = (num: number) => {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0
        }).format(num);
    };


    return (
        <Card className="border-0 rounded-3">
            <Card.Body>
                <Row className="mb-3">
                    <Col>
                        <h5 className="mb-0">Динамика платежей</h5>
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
                                    name === 'total_amount' ? formatCurrency(value) :
                                    name === 'average_check' ? formatCurrency(value) :
                                    formatNumber(value),
                                    name === 'total_amount' ? 'Общая сумма' :
                                    name === 'count' ? 'Количество платежей' :
                                    'Средний чек'
                                ]}
                            />
                            <Legend 
                                formatter={(value) => 
                                    value === 'total_amount' ? 'Общая сумма' :
                                    value === 'count' ? 'Количество платежей' :
                                    'Средний чек'
                                }
                            />
                            <Line 
                                type="monotone" 
                                dataKey="total_amount" 
                                stroke="#28a745" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="count" 
                                stroke="#007bff" 
                                strokeWidth={2}
                                dot={{ r: 4 }}
                            />
                            <Line 
                                type="monotone" 
                                dataKey="average_check" 
                                stroke="#ffc107" 
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

export default PaymentsChart;
