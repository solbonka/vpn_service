import {Server, People, Activity, HddStack, Cpu} from 'react-bootstrap-icons';
import {Row, Col, Badge, ProgressBar} from 'react-bootstrap';

interface ServerItemProps {
    server: any;
}

function formatBytes(bytes: number) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function ServerItem({server}: ServerItemProps) {
    const sys = server.system;
    const cpuUsage = sys.cpu_usage; // %
    const memUsage = Math.round((sys.mem_used / sys.mem_total) * 100);
    const trafficIn = sys.incoming_bandwidth_speed; // Kbps или Mbps зависит от бэка
    const trafficOut = sys.outgoing_bandwidth_speed;

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
                            <h3 className="h5 fw-bold mb-1">{server.name}</h3>
                            <p className="text-muted mb-0">{server.code}</p>
                        </div>
                        <div className="ms-3">
                            <Badge bg={server.is_active ? "success" : "secondary"}>
                                {server.is_active ? 'Активно' : 'Не активно'}
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
                                {sys.users_active}/{sys.total_user}
                            </p>
                            <small className="text-success">Онлайн: {sys.online_users}</small>
                        </Col>

                        {/* Трафик */}
                        <Col xs={6} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <HddStack size={16} className="me-1"/>
                                <small>Трафик</small>
                            </div>
                            <p className="h6 fw-bold mb-0">
                                ↑ {trafficOut} / ↓ {trafficIn} Kbps
                            </p>
                        </Col>

                        {/* CPU */}
                        <Col xs={12} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <Cpu size={16} className="me-1"/>
                                <small>CPU</small>
                            </div>
                            <div className="d-flex align-items-center">
                                <ProgressBar
                                    now={cpuUsage}
                                    variant={cpuUsage > 80 ? "danger" : cpuUsage > 50 ? "warning" : "success"}
                                    className="flex-grow-1 me-2"
                                />
                                <span className="fw-bold">{cpuUsage}%</span>
                            </div>
                        </Col>

                        {/* Память */}
                        <Col xs={12} md={3}>
                            <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                <Activity size={16} className="me-1"/>
                                <small>RAM</small>
                            </div>
                            <div className="d-flex align-items-center">
                                <ProgressBar
                                    now={memUsage}
                                    variant={memUsage > 80 ? "danger" : memUsage > 50 ? "warning" : "success"}
                                    className="flex-grow-1 me-2"
                                />
                                <span className="fw-bold">{memUsage}%</span>
                            </div>
                            <small>{formatBytes(sys.mem_used)} / {formatBytes(sys.mem_total)}</small>
                        </Col>
                    </Row>
                </Col>
            </Row>
        </div>
    );
}

export default ServerItem;