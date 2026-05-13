import {Server, People, Activity, HddStack, Cpu} from 'react-bootstrap-icons';
import {Row, Col, Badge, ProgressBar} from 'react-bootstrap';

interface RemnawaveHostItemProps {
    host: any;
}

function formatBytes(bytes: number) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function RemnawaveHostItem({host}: RemnawaveHostItemProps) {
    const nodesCount = host.nodes_count || 0;
    const activeNodes = host.nodes?.filter((node: any) => node.status === 'online').length || 0;

    return (
        <div className="w-100">
            <Row className="align-items-center">
                {/* Левая часть */}
                <Col xs={12} lg={6}>
                    <div className="d-flex align-items-center">
                        <div className="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <Server size={24} className="text-primary"/>
                        </div>
                        <div>
                            <h3 className="h5 fw-bold mb-1">{host.name}</h3>
                            <p className="text-muted mb-0">{host.address}</p>
                        </div>
                        <div className="ms-3">
                            <Badge bg={host.status === "active" ? "success" : "secondary"}>
                                {host.status === "active" ? 'Активно' : 'Не активно'}
                            </Badge>
                        </div>
                    </div>
                </Col>

                {/* Правая часть */}
                <Col xs={12} lg={6}>
                    <Row className="text-center">
                        {/* Пользователи */}
                        <Col xs={6} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <People size={16} className="me-1"/>
                                <small>Пользователи</small>
                            </div>
                            <p className="h6 fw-bold mb-0">
                                {host.online_users}/{host.total_users}
                            </p>
                            <small className="text-success">Онлайн на хосте</small>
                        </Col>

                        {/* Безопасность */}
                        <Col xs={6} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <HddStack size={16} className="me-1"/>
                                <small>Безопасность</small>
                            </div>
                            <p className="h6 fw-bold mb-0">
                                {host.security_layer}
                            </p>
                        </Col>

                        {/* Позиция */}
                        <Col xs={12} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <Cpu size={16} className="me-1"/>
                                <small>Позиция</small>
                            </div>
                            <p className="h6 fw-bold mb-0">
                                #{host.view_position}
                            </p>
                        </Col>

                        {/* Скрытый */}
                        <Col xs={12} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <Activity size={16} className="me-1"/>
                                <small>Статус</small>
                            </div>
                            <p className="h6 fw-bold mb-0">
                                {host.is_hidden ? 'Скрыт' : 'Видимый'}
                            </p>
                        </Col>
                    </Row>
                </Col>
            </Row>
        </div>
    );
}

export default RemnawaveHostItem;
