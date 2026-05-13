import React from 'react';
import { Form } from 'react-bootstrap';
import { useChartsContext } from '../../../contexts/ChartsContext';

const DateRangePicker: React.FC = () => {
    const { period, startDate, endDate, setPeriod, setStartDate, setEndDate } = useChartsContext();
    const handlePeriodChange = (newPeriod: string) => {
        setPeriod(newPeriod);
        
        // Сбрасываем даты при выборе предустановленного периода
        if (newPeriod !== 'custom') {
            setStartDate('');
            setEndDate('');
        }
    };

    const handleCustomDateChange = () => {
        if (startDate && endDate) {
            setPeriod('custom');
        }
    };

    return (
        <div className="d-flex gap-2 align-items-center">
            <Form.Select 
                size="sm" 
                value={period} 
                onChange={(e) => handlePeriodChange(e.target.value)}
                style={{ width: 'auto' }}
            >
                <option value="7d">7 дней</option>
                <option value="30d">30 дней</option>
                <option value="90d">90 дней</option>
                <option value="custom">Выбрать даты</option>
            </Form.Select>
            
            {period === 'custom' && (
                <>
                    <Form.Control
                        type="date"
                        size="sm"
                        value={startDate}
                        onChange={(e) => {
                            setStartDate(e.target.value);
                            handleCustomDateChange();
                        }}
                        style={{ width: 'auto' }}
                        placeholder="От"
                    />
                    <span className="text-muted">—</span>
                    <Form.Control
                        type="date"
                        size="sm"
                        value={endDate}
                        onChange={(e) => {
                            setEndDate(e.target.value);
                            handleCustomDateChange();
                        }}
                        style={{ width: 'auto' }}
                        placeholder="До"
                    />
                </>
            )}
        </div>
    );
};

export default DateRangePicker;
