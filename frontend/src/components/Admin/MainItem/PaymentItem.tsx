import {Card, Col, Row} from "react-bootstrap";
import {CurrencyDollar, Receipt, Calculator} from "react-bootstrap-icons";
import React from "react";
import {TrendingUp} from "lucide-react";

interface PaymentItemProps {
    PercentageIndicator: React.FC<{
        value: number;
        isPositive: boolean
    }>;
    payments: any;
}

function PaymentItem({PercentageIndicator, payments}: PaymentItemProps) {
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0
        }).format(amount);
    };

    return (
        <Row>
            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-success bg-opacity-10 p-3 rounded me-3">
                            <CurrencyDollar size={24} className="text-success"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-success">{formatCurrency(payments.total_income.count)}</h4>
                            <p className="text-muted mb-0 small">Общий доход</p>
                            <PercentageIndicator
                                value={payments.total_income.growth}
                                isPositive={payments.total_income.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <TrendingUp size={24} className="text-primary"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-primary">{formatCurrency(payments.monthly_income.count)}</h4>
                            <p className="text-muted mb-0 small">Доход за месяц</p>
                            <PercentageIndicator
                                value={payments.monthly_income.growth}
                                isPositive={payments.monthly_income.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-info bg-opacity-10 p-3 rounded me-3">
                            <Receipt size={24} className="text-info"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-info">{formatCurrency(payments.average_check.count)}</h4>
                            <p className="text-muted mb-0 small">Средний чек</p>
                            <PercentageIndicator
                                value={payments.average_check.growth}
                                isPositive={payments.average_check.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-warning bg-opacity-10 p-3 rounded me-3">
                            <Calculator size={24} className="text-warning"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-warning">{formatCurrency(payments.monthly_average_check.count)}</h4>
                            <p className="text-muted mb-0 small">Средний чек за месяц</p>
                            <PercentageIndicator
                                value={payments.monthly_average_check.growth}
                                isPositive={payments.monthly_average_check.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>
        </Row>
    );
}

export default PaymentItem;