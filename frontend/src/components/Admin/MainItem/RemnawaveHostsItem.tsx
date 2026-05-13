import {Card, Col, Row} from "react-bootstrap";
import {People, PersonCheck, Server, Globe} from "react-bootstrap-icons";
import React from "react";

interface RemnawaveHostsItemProps {
    PercentageIndicator: React.FC<{
        value: number;
        isPositive: boolean
    }>;
    formatNumber: (num: number) => string;
    hosts: any;
}

function RemnawaveHostsItem({ PercentageIndicator, formatNumber, hosts }: RemnawaveHostsItemProps) {
    return (
        <Row>
            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <Server size={24} className="text-primary"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-primary">{hosts.hosts.active}/{hosts.hosts.total}</h4>
                            <p className="text-muted mb-0 small">Хосты онлайн</p>
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-info bg-opacity-10 p-3 rounded me-3">
                            <People size={24} className="text-info"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-info">{formatNumber(hosts.users.total.count)}</h4>
                            <p className="text-muted mb-0 small">Всего пользователей</p>
                            <PercentageIndicator
                                value={hosts.users.total.growth}
                                isPositive={hosts.users.total.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-success bg-opacity-10 p-3 rounded me-3">
                            <PersonCheck size={24} className="text-success"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-success">{formatNumber(hosts.users.active.count)}</h4>
                            <p className="text-muted mb-0 small">Активных пользователей</p>
                            <PercentageIndicator
                                value={hosts.users.active.growth}
                                isPositive={hosts.users.active.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>

            <Col xs={12} sm={6} lg={3} className="mb-3">
                <Card className="h-100 shadow-sm border-0">
                    <Card.Body className="d-flex align-items-center">
                        <div className="bg-warning bg-opacity-10 p-3 rounded me-3">
                            <Globe size={24} className="text-warning"/>
                        </div>
                        <div>
                            <h4 className="h3 fw-bold mb-0 text-warning">{formatNumber(hosts.users.online.count)}</h4>
                            <p className="text-muted mb-0 small">Пользователей онлайн</p>
                            <PercentageIndicator
                                value={hosts.users.online.growth}
                                isPositive={hosts.users.online.growth >= 0}
                            />
                        </div>
                    </Card.Body>
                </Card>
            </Col>
        </Row>
    );
}

export default RemnawaveHostsItem;
