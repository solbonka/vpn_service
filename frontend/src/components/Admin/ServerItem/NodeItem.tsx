import {Badge, Col, Row} from "react-bootstrap";
import {Server} from 'react-bootstrap-icons';

interface Node {
    id: number;
    name: string;
    address: string;
    status: string;
    usage_coefficient: number;
    xray_version: string;
    message?: string | null;
}

interface NodeItemProps {
    nodes: Node[];
}

function NodeItem({nodes}: NodeItemProps) {
    return (
        <>
            <h4 className="h6 fw-semibold mb-3">Ноды сервера</h4>

            {nodes.map((node) => (
                <div key={node.id} className="w-100 border rounded p-3 shadow-sm mb-3">
                    <Row className="align-items-center">
                        {/* Левая часть — инфо о сервере */}
                        <Col xs={12} lg={6}>
                            <div className="d-flex align-items-center">
                                <div className="bg-info bg-opacity-10 p-2 rounded me-3">
                                    <Server size={24} className="text-info"/>
                                </div>
                                <div>
                                    <h3 className="h6 fw-bold mb-1">{node.name}</h3>
                                    <p className="text-muted mb-0">{node.address}</p>
                                    <small className="text-muted">Xray {node.xray_version}</small>
                                </div>
                                <div className="ms-3">
                                    <Badge
                                        bg={
                                            node.status === "connected"
                                                ? "success"
                                                : node.status === "error"
                                                    ? "danger"
                                                    : "secondary"
                                        }
                                    >
                                        {node.status}
                                    </Badge>
                                </div>
                            </div>
                        </Col>

                        {/* Правая часть — нагрузка (пока нет реальных данных, можно условно) */}
                        <Col xs={12} lg={6}>
                            <Row className="text-center">
                                {/* Коэффициент использования */}
                                <Col xs={12}>
                                    <div className="d-flex align-items-center justify-content-center text-muted mb-1">
                                        <small>Коэффициент нагрузки</small>
                                    </div>
                                    <p className="h6 fw-bold mb-0">{node.usage_coefficient}</p>
                                </Col>
                            </Row>
                        </Col>
                    </Row>

                    {/* Ошибка, если есть */}
                    {node.message && (
                        <div className="mt-2 text-danger small">
                            {node.message}
                        </div>
                    )}
                </div>
            ))}
        </>
    );
}

export default NodeItem;