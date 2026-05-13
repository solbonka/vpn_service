import {Badge, Col, Row, ProgressBar} from "react-bootstrap";
import {Server, ArrowDown, ArrowUp, Speedometer} from 'react-bootstrap-icons';

interface NodeUsage {
    download_speed_bps: number;
    upload_speed_bps: number;
    total_speed_bps: number;
}

interface RemnawaveNode {
    id: string;
    name: string;
    address: string;
    port: number;
    status: string;
    users_online: number;
    xray_version: string;
    is_connected: boolean;
    is_xray_running: boolean;
    country_code: string;
    created_at: string;
    updated_at: string;
    usage: NodeUsage;
}

interface RemnawaveNodeItemProps {
    nodes: RemnawaveNode[];
}

function formatSpeed(bps: number): string {
    // Конвертируем байты/сек в мегабиты/сек
    // 1 байт = 8 бит, 1 Мбит = 1,000,000 бит
    const mbps = (bps * 8) / 1000000;
    
    if (mbps === 0) return '0 Мбит/с';
    if (mbps < 0.01) return mbps.toFixed(4) + ' Мбит/с';
    if (mbps < 1) return mbps.toFixed(2) + ' Мбит/с';
    return mbps.toFixed(2) + ' Мбит/с';
}

function getLoadStatus(totalSpeedBps: number): { label: string; color: string; bgColor: string } {
    // Конвертируем в Мбит/с
    const mbps = (totalSpeedBps * 8) / 1000000;
    
    if (mbps === 0) {
        return { label: 'Нет нагрузки', color: 'secondary', bgColor: 'secondary' };
    } else if (mbps < 100) {
        return { label: 'Низкая нагрузка', color: 'success', bgColor: 'success' };
    } else if (mbps < 500) {
        return { label: 'Средняя нагрузка', color: 'warning', bgColor: 'warning' };
    } else if (mbps < 1000) {
        return { label: 'Высокая нагрузка', color: 'orange', bgColor: 'warning' };
    } else {
        return { label: 'Критическая нагрузка', color: 'danger', bgColor: 'danger' };
    }
}

function getSpeedPercentage(speedBps: number): number {
    // Конвертируем в Мбит/с и вычисляем процент от 1000 Мбит/с (1 Гбит/с)
    const mbps = (speedBps * 8) / 1000000;
    const percentage = (mbps / 1000) * 100;
    return Math.min(percentage, 100); // Максимум 100%
}

function getSpeedVariant(speedBps: number): "success" | "warning" | "danger" {
    const mbps = (speedBps * 8) / 1000000;
    
    if (mbps > 800) return "danger";    // > 800 Мбит/с - красный
    if (mbps > 500) return "warning";   // > 500 Мбит/с - желтый
    return "success";                    // <= 500 Мбит/с - зеленый
}

function RemnawaveNodeItem({nodes}: RemnawaveNodeItemProps) {
    return (
        <>
            <h4 className="h6 fw-semibold mb-3">Узлы хоста</h4>

            {nodes.map((node) => {
                const usage = node.usage;
                const loadStatus = getLoadStatus(usage?.total_speed_bps || 0);
                
                return (
                <div key={node.id} className="w-100 border rounded p-3 shadow-sm mb-3">
                    <Row className="align-items-center">
                        {/* Левая часть — инфо о узле */}
                        <Col xs={12} lg={6}>
                            <div className="d-flex align-items-center">
                                <div className="bg-info bg-opacity-10 p-2 rounded me-3">
                                    <Server size={24} className="text-info"/>
                                </div>
                                <div>
                                    <h3 className="h6 fw-bold mb-1">{node.name}</h3>
                                    <p className="text-muted mb-0">{node.address}</p>
                                    <small className="text-muted">Xray {node.xray_version}</small>
                                    <br/>
                                    <small className="text-muted">Страна: {node.country_code}</small>
                                </div>
                                <div className="ms-3 d-flex flex-column gap-1">
                                    <Badge
                                        bg={
                                            node.status === "online"
                                                ? "success"
                                                : node.status === "offline"
                                                    ? "danger"
                                                    : "secondary"
                                        }
                                    >
                                        {node.status}
                                    </Badge>
                                    <Badge bg={loadStatus.bgColor}>
                                        {loadStatus.label}
                                    </Badge>
                                </div>
                            </div>
                        </Col>

                        {/* Правая часть — статистика скорости */}
                        <Col xs={12} lg={6}>
                            <Row className="align-items-center">
                                {/* Скорость скачивания */}
                                <Col xs={6} md={3} className="text-center">
                                    <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                        <ArrowDown size={14} className="me-1 text-success"/>
                                        <small>Скачивание</small>
                                    </div>
                                    <p className="h6 fw-bold mb-0 text-success">
                                        {usage ? formatSpeed(usage.download_speed_bps) : '0 Мбит/с'}
                                    </p>
                                </Col>

                                {/* Скорость загрузки */}
                                <Col xs={6} md={3} className="text-center">
                                    <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                        <ArrowUp size={14} className="me-1 text-primary"/>
                                        <small>Загрузка</small>
                                    </div>
                                    <p className="h6 fw-bold mb-0 text-primary">
                                        {usage ? formatSpeed(usage.upload_speed_bps) : '0 Мбит/с'}
                                    </p>
                                </Col>

                                {/* Общая скорость - только значение */}
                                <Col xs={6} md={3} className="text-center">
                                    <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                        <Speedometer size={14} className="me-1 text-info"/>
                                        <small>Общая</small>
                                    </div>
                                    <p className="h6 fw-bold mb-0 text-info">
                                        {usage ? formatSpeed(usage.total_speed_bps) : '0 Мбит/с'}
                                    </p>
                                </Col>

                                {/* Прогресс-бар нагрузки */}
                                <Col xs={6} md={3}>
                                    <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                        <Speedometer size={14} className="me-1"/>
                                        <small>Нагрузка</small>
                                    </div>
                                    <div className="d-flex align-items-center">
                                        <ProgressBar
                                            now={usage ? getSpeedPercentage(usage.total_speed_bps) : 0}
                                            variant={usage ? getSpeedVariant(usage.total_speed_bps) : "success"}
                                            className="flex-grow-1 me-2"
                                            style={{height: '8px'}}
                                        />
                                        <span className="fw-bold" style={{minWidth: '35px', fontSize: '0.875rem'}}>
                                            {usage ? getSpeedPercentage(usage.total_speed_bps).toFixed(0) : 0}%
                                        </span>
                                    </div>
                                    <small className="text-muted" style={{fontSize: '0.7rem'}}>
                                        {usage ? formatSpeed(usage.total_speed_bps) : '0 Мбит/с'} / 1 Гб/с
                                    </small>
                                </Col>
                            </Row>
                        </Col>
                    </Row>
                </div>
                );
            })}
        </>
    );
}

export default RemnawaveNodeItem;
