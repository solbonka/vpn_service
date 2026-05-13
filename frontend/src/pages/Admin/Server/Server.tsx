import {Accordion, Container} from 'react-bootstrap';
import ServerItem from "../../../components/Admin/ServerItem/ServerItem";
import NodeItem from "../../../components/Admin/ServerItem/NodeItem";
import LoadingSpinner from "../../../components/UI/LoadingSpinner"
import api from "../../../api";
import {useState, useEffect} from 'react';

interface Node {
    id: number;
    name: string;
    address: string;
    status: string;
    usage_coefficient: number;
    xray_version: string;
    message?: string | null;
}

interface ServerType {
    id: number;
    nodes: Node[];
}

function Server() {
    const [servers, setServers] = useState<ServerType[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        let timeout: NodeJS.Timeout;

        const fetchServers = () => {
            api.servers.getServers()
                .then((res: any) => {
                    setServers(res.data.data);
                    setLoading(false);
                })
                .catch((err: any) => {
                    console.error('Ошибка загрузки серверов:', err);
                })
                .finally(() => {
                    timeout = setTimeout(fetchServers, 10000);
                });
        };

        fetchServers();

        return () => clearTimeout(timeout);
    }, []);

    return (
        <>
            <title>Серверы</title>

            {loading ? (
                <LoadingSpinner fullscreen/>
            ) : (
                <Container className="mt-2">
                    <h2 className="h2 fw-bold text-dark mb-2">Серверы</h2>
                    <p className="text-muted mb-4">Управление основными серверами и их нодами</p>
                    {servers.map((server, index) => (
                        <Accordion
                            key={server.id}
                            className="shadow-sm mb-3"
                            defaultActiveKey={['0']}
                            alwaysOpen
                        >
                            <Accordion.Item eventKey={String(index)}>
                                <Accordion.Header>
                                    <ServerItem server={server}/>
                                </Accordion.Header>
                                <Accordion.Body>
                                    <NodeItem nodes={server.nodes}/>
                                </Accordion.Body>
                            </Accordion.Item>
                        </Accordion>
                    ))}
                </Container>
            )}
        </>
    );
}

export default Server;