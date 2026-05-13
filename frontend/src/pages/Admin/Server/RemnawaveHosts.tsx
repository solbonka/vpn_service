import {Accordion, Container, Badge} from 'react-bootstrap';
import RemnawaveHostItem from "../../../components/Admin/ServerItem/RemnawaveHostItem";
import RemnawaveNodeItem from "../../../components/Admin/ServerItem/RemnawaveNodeItem";
import LoadingSpinner from "../../../components/UI/LoadingSpinner"
import api from "../../../api";
import {useState, useEffect} from 'react';
import {ArrowClockwise} from 'react-bootstrap-icons';

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
}

interface RemnawaveHost {
    id: string;
    name: string;
    address: string;
    status: string;
    security_layer: string;
    total_users: number;
    online_users: number;
    nodes_count: number;
    nodes: RemnawaveNode[];
    view_position: number;
    is_hidden: boolean;
}

function RemnawaveHosts() {
    const [hosts, setHosts] = useState<RemnawaveHost[]>([]);
    const [loading, setLoading] = useState(true);
    const [lastUpdate, setLastUpdate] = useState<Date>(new Date());

    useEffect(() => {
        let timeout: NodeJS.Timeout;

        // Функция для загрузки хостов с usage данными
        const fetchHosts = () => {
            api.remnawave.getHosts()
                .then((res: any) => {
                    setHosts(res.data.data);
                    setLoading(false);
                    setLastUpdate(new Date());
                })
                .catch((err: any) => {
                    console.error('Ошибка загрузки хостов Remnawave:', err);
                })
                .finally(() => {
                    timeout = setTimeout(fetchHosts, 5000); // Обновляем каждые 5 секунд для актуальных данных трафика
                });
        };

        fetchHosts();

        return () => {
            clearTimeout(timeout);
        };
    }, []);

    return (
        <>
            <title>Хосты Remnawave</title>

            {loading ? (
                <LoadingSpinner fullscreen/>
            ) : (
                <Container className="mt-2">
                    <div className="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 className="h2 fw-bold text-dark mb-2">Хосты Remnawave</h2>
                            <p className="text-muted mb-0">Управление хостами и их узлами</p>
                        </div>
                        <div className="text-end">
                            <Badge bg="light" text="dark" className="d-flex align-items-center">
                                <ArrowClockwise className="me-2" size={14}/>
                                <span>Последнее обновление: {lastUpdate.toLocaleTimeString()}</span>
                            </Badge>
                            <small className="text-muted d-block mt-1">Автообновление каждые 5 сек</small>
                        </div>
                    </div>

                    {hosts.map((host, index) => (
                        <Accordion
                            key={host.id}
                            className="shadow-sm mb-3"
                            defaultActiveKey={['0']}
                            alwaysOpen
                        >
                            <Accordion.Item eventKey={String(index)}>
                                <Accordion.Header>
                                    <RemnawaveHostItem host={host}/>
                                </Accordion.Header>
                                <Accordion.Body>
                                    <RemnawaveNodeItem nodes={host.nodes}/>
                                </Accordion.Body>
                            </Accordion.Item>
                        </Accordion>
                    ))}
                </Container>
            )}
        </>
    );
}

export default RemnawaveHosts;
