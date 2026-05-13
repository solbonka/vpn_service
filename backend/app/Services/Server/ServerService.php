<?php

namespace App\Services\Server;

use App\Models\Server;
use App\Services\Marzban\MarzbanService;
use App\Services\Remnawave\RemnawaveService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class ServerService
{
    private MarzbanService $marzbanService;
    private RemnawaveService $remnawaveService;

    public function __construct(MarzbanService $marzbanService, RemnawaveService $remnawaveService)
    {
        $this->marzbanService = $marzbanService;
        $this->remnawaveService = $remnawaveService;
    }

    /**
     * @throws GuzzleException
     */
    public function getServerStatisticsFromMarzban(Server $server): ?array
    {
        try {
            $response = $this->marzbanService->getServerStatistics($server);

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();

                return json_decode($body, true);
            }

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }

        return null;
    }

    /**
     * @throws GuzzleException
     */
    public function getNodesStatisticsFromMarzban(Server $server): ?array
    {
        try {
            $response = $this->marzbanService->getNodesStatistics($server);

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();

                return json_decode($body, true);
            }

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }

        return null;
    }

    /**
     * @throws GuzzleException
     */
    public function getRemnawaveHostsStatistics(): array
    {
        try {
            $hostsResponse = $this->remnawaveService->getHosts();
            $hosts = $hostsResponse->getActiveHosts();

            // Получаем usage данные для всех нод
            $usageData = $this->getRemnawaveNodesUsageRealtime();

            // Создаем map usage по nodeUuid для быстрого поиска
            $usageMap = [];
            foreach ($usageData as $usage) {
                $usageMap[$usage['nodeUuid']] = $usage;
            }

            $hostsData = [];

            foreach ($hosts as $host) {
                $totalUsers = 0;
                $onlineUsers = 0;
                $nodesData = [];

                foreach ($host->nodes as $nodeUuid) {
                    try {
                        $nodeResponse = $this->remnawaveService->getNodeStatistics($nodeUuid);
                        if ($nodeResponse->getStatusCode() === 200) {
                            $nodeData = json_decode($nodeResponse->getBody()->getContents(), true)['response'];

                            $usersOnline = $nodeData['usersOnline'] ?? 0;
                            $totalUsers += $usersOnline;
                            $onlineUsers += $usersOnline;

                            $nodeUsage = $usageMap[$nodeUuid] ?? null;

                            $nodesData[] = [
                                'id' => $nodeData['uuid'],
                                'name' => $nodeData['name'],
                                'address' => $nodeData['address'],
                                'port' => $nodeData['port'] ?? null,
                                'status' => $nodeData['isNodeOnline'] ? 'online' : 'offline',
                                'users_online' => $usersOnline,
                                'xray_version' => $nodeData['xrayVersion'],
                                'is_connected' => $nodeData['isConnected'],
                                'is_xray_running' => $nodeData['isXrayRunning'],
                                'country_code' => $nodeData['countryCode'],
                                'created_at' => $nodeData['createdAt'],
                                'updated_at' => $nodeData['updatedAt'],
                                'usage' => $nodeUsage
                            ];
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Не удалось получить статистику узла', [
                            'node_uuid' => $nodeUuid,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $hostsData[] = [
                    'id' => $host->uuid,
                    'name' => $host->remark,
                    'address' => $host->address,
                    'status' => $host->isDisabled ? 'disabled' : 'active',
                    'security_layer' => $host->securityLayer,
                    'total_users' => $totalUsers,
                    'online_users' => $onlineUsers,
                    'nodes_count' => count($nodesData),
                    'nodes' => $nodesData,
                    'view_position' => $host->viewPosition,
                    'is_hidden' => $host->isHidden
                ];
            }

            return $hostsData;

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении статистики хостов Remnawave', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getRemnawaveHostsMetrics(): array
    {
        try {
            $metricsResponse = $this->remnawaveService->getNodesMetrics();

            if ($metricsResponse->getStatusCode() === 200) {
                $metricsData = json_decode($metricsResponse->getBody()->getContents(), true)['response'];

                $totalUsers = 0;
                $onlineUsers = 0;
                $nodesData = [];

                foreach ($metricsData['nodes'] as $node) {
                    $usersOnline = $node['usersOnline'] ?? 0;
                    $totalUsers += $usersOnline;
                    $onlineUsers += $usersOnline;

                    $nodesData[] = [
                        'node_uuid' => $node['nodeUuid'],
                        'node_name' => $node['nodeName'],
                        'country_emoji' => $node['countryEmoji'],
                        'provider_name' => $node['providerName'],
                        'users_online' => $usersOnline,
                        'inbounds_stats' => $node['inboundsStats'] ?? [],
                        'outbounds_stats' => $node['outboundsStats'] ?? []
                    ];
                }

                return [
                    'total_users' => $totalUsers,
                    'online_users' => $onlineUsers,
                    'active_nodes' => count($nodesData),
                    'nodes' => $nodesData
                ];
            }

            return [
                'total_users' => 0,
                'online_users' => 0,
                'active_nodes' => 0,
                'nodes' => []
            ];

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении метрик хостов Remnawave', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_users' => 0,
                'online_users' => 0,
                'active_nodes' => 0,
                'nodes' => []
            ];
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getRemnawaveNodesUsageRealtime(): array
    {
        try {
            $usageResponse = $this->remnawaveService->getNodesUsageRealtime();

            if ($usageResponse->getStatusCode() === 200) {
                $usageData = json_decode($usageResponse->getBody()->getContents(), true)['response'];

                return $usageData;
            }

            return [];

        } catch (\Exception $e) {
            \Log::error('Ошибка при получении realtime usage нод Remnawave', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }
}
