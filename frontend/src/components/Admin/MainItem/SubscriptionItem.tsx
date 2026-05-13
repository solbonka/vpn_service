import {Card, Col, Row, OverlayTrigger, Popover} from "react-bootstrap";
import {CreditCard, PersonPlus, PersonX, InfoCircle} from "react-bootstrap-icons";
import React from "react";

interface SubscriptionItemProps {
    PercentageIndicator: React.FC<{
        value: number;
        isPositive: boolean
    }>;
    formatNumber: (num: number) => string;
    subscriptions: any;
}

function SubscriptionItem({PercentageIndicator, formatNumber, subscriptions}: SubscriptionItemProps) {
    const popoverContent = (
        <Popover id="subscription-metrics-popover" style={{ maxWidth: "none" }}>
            <Popover.Body className="text-start">
                • Учитываются только платные подписки <br/>
                • Рост считается по формуле: <br/>
                (текущий период - предыдущий) / предыдущий × 100% <br/>
                • Периоды сравниваются помесячно
            </Popover.Body>
        </Popover>
    );

    return (
        <>
            <div className="d-flex align-items-center mb-3">
                <h3 className="h4 fw-semibold mb-0 me-2">Подписки</h3>
                <OverlayTrigger
                    placement="right"
                    delay={{show: 250, hide: 400}}
                    overlay={popoverContent}
                >
                    <InfoCircle
                        size={16}
                        className="text-muted"
                        style={{cursor: 'pointer'}}
                    />
                </OverlayTrigger>
            </div>

            <Row>
                <Col xs={12} sm={6} lg={4} className="mb-3">
                    <Card className="h-100 shadow-sm border-0">
                        <Card.Body className="d-flex align-items-center">
                            <div className="bg-success bg-opacity-10 p-3 rounded me-3">
                                <CreditCard size={24} className="text-success"/>
                            </div>
                            <div>
                                <h4 className="h3 fw-bold mb-0 text-success">{formatNumber(subscriptions.active.count)}</h4>
                                <p className="text-muted mb-0 small">Активных подписок</p>
                                <PercentageIndicator
                                    value={subscriptions.active.growth}
                                    isPositive={subscriptions.active.growth >= 0}
                                />
                            </div>
                        </Card.Body>
                    </Card>
                </Col>

                <Col xs={12} sm={6} lg={4} className="mb-3">
                    <Card className="h-100 shadow-sm border-0">
                        <Card.Body className="d-flex align-items-center">
                            <div className="bg-danger bg-opacity-10 p-3 rounded me-3">
                                <PersonX size={24} className="text-danger"/>
                            </div>
                            <div>
                                <h4 className="h3 fw-bold mb-0 text-danger">{formatNumber(subscriptions.blocked.count)}</h4>
                                <p className="text-muted mb-0 small">Заблокированных</p>
                                <PercentageIndicator
                                    value={subscriptions.blocked.growth}
                                    isPositive={subscriptions.blocked.growth >= 0}
                                />
                            </div>
                        </Card.Body>
                    </Card>
                </Col>

                <Col xs={12} sm={6} lg={4} className="mb-3">
                    <Card className="h-100 shadow-sm border-0">
                        <Card.Body className="d-flex align-items-center">
                            <div className="bg-primary bg-opacity-10 p-3 rounded me-3">
                                <PersonPlus size={24} className="text-primary"/>
                            </div>
                            <div>
                                <h4 className="h3 fw-bold mb-0 text-primary">{formatNumber(subscriptions.new.count)}</h4>
                                <p className="text-muted mb-0 small">Новых за месяц</p>
                                <PercentageIndicator
                                    value={subscriptions.new.growth}
                                    isPositive={subscriptions.new.growth >= 0}
                                />
                            </div>
                        </Card.Body>
                    </Card>
                </Col>
            </Row>
        </>
    );
}

export default SubscriptionItem;