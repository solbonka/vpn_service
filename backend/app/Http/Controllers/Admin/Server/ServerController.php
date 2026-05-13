<?php

namespace App\Http\Controllers\Admin\Server;

use App\Actions\Metrics\ServerMetricAction;
use App\DTO\Actions\Metrics\ServerMetricDto;
use App\DTO\Charts\ChartDataRequestDto;
use App\Helpers\Metrics\ServerMetricHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Charts\ChartDataRequest;
use App\Http\Resources\Metrics\ServerMetricResource;
use App\Http\Resources\Remnawave\RemnawaveHostResource;
use App\Http\Resources\Server\ServerResource;
use App\Models\Server;
use App\Services\Charts\UserChartService;
use App\Services\Server\ServerService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServerController extends Controller
{
    public function __construct(
        private readonly ServerService $serverService,
        private readonly UserChartService $chartService
    ) {}

    /**
     * Получить серверы Marzban
     * @throws GuzzleException
     */
    public function getMarzbanServers(): AnonymousResourceCollection
    {
        $servers = Server::query()->orderBy('order')->get();

        $serverData = [];

        foreach ($servers as $server) {
            $systemStats = $this->serverService->getServerStatisticsFromMarzban($server);
            $nodesStats = $this->serverService->getNodesStatisticsFromMarzban($server);

            $serverData[] = [
                'id' => $server->id,
                'name' => $server->name,
                'code' => $server->code,
                'is_active' => $server->is_active,
                'system' => $systemStats,
                'nodes' => $nodesStats
            ];
        }

        return ServerResource::collection($serverData);
    }

    /**
     * Получить метрики серверов Marzban
     * @throws GuzzleException
     */
    public function getMarzbanMetrics(ServerMetricAction $action): ServerMetricResource
    {
        $servers = Server::query()->orderBy('order')->get();

        foreach ($servers as $server) {
            $systemStats = $this->serverService->getServerStatisticsFromMarzban($server);

            if (!$systemStats) {
                continue;
            }

            $action->execute(new ServerMetricDto(
                serverId: $server->id,
                totalUsers: $systemStats['total_user'],
                activeUsers: $systemStats['users_active'],
                onlineUsers: $systemStats['online_users']
            ));
        }

        return ServerMetricHelper::aggregate();
    }

    public function getUsersChartData(ChartDataRequest $request): JsonResponse
    {
        $requestDto = ChartDataRequestDto::fromRequest($request->validated());
        $response = $this->chartService->getChartData($requestDto);

        return response()->json($response->toArray());
    }

    /**
     * Получить хосты Remnawave с данными трафика в реальном времени
     * @throws GuzzleException
     */
    public function getRemnawaveHosts(): JsonResponse
    {
        $hostsData = $this->serverService->getRemnawaveHostsStatistics();

        return response()->json([
            'success' => true,
            'data' => RemnawaveHostResource::collection($hostsData),
            'total' => count($hostsData)
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getRemnawaveHostsMetrics(): JsonResponse
    {
        $metricsData = $this->serverService->getRemnawaveHostsMetrics();

        return response()->json([
            'success' => true,
            'data' => $metricsData
        ]);
    }
}
