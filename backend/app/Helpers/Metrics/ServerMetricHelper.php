<?php

namespace App\Helpers\Metrics;

use App\Http\Resources\Metrics\ServerMetricResource;
use App\Models\Server;
use App\Models\ServerMetric;
use Carbon\Carbon;

class ServerMetricHelper
{
    public static function aggregate(): ServerMetricResource
    {
        $servers = Server::query()->orderBy('order')->get();

        $totalServers = $servers->count();
        $activeServers = $servers->where('is_active', true)->count();

        $latestMetric = $servers->first()->metrics()->latest()->first();
        $totalUsers = $latestMetric->total_users;
        $activeUsers = $latestMetric->active_users;

        $onlineUsers = 0;

        foreach ($servers as $server) {
            $onlineUsers += $server->metrics()->latest()->first()->online_users;
        }

        $monthStart = Carbon::now()->startOfMonth();

        $baseOnlineUsers = 0;

        foreach ($servers as $server) {
            $firstMetricOfDay = $server->metrics()
                ->where('created_at', '>=', $monthStart)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($firstMetricOfDay) {
                $baseOnlineUsers += $firstMetricOfDay->online_users;
            }
        }

        $baseMetricToday = ServerMetric::where('created_at', '>=', $monthStart)
            ->orderBy('created_at')
            ->first();

        $baseTotalUsers = $baseMetricToday?->total_users ?? $totalUsers;
        $baseActiveUsers = $baseMetricToday?->active_users ?? $activeUsers;

        $calculateGrowth = function ($current, $base) {
            if ($base == 0) return $current > 0 ? 100 : 0;
            return round((($current - $base) / $base) * 100, 1);
        };

        return new ServerMetricResource([
            'servers_total' => $totalServers,
            'servers_active' => $activeServers,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'online_users' => $onlineUsers,
            'total_users_growth' => $calculateGrowth($totalUsers, $baseTotalUsers),
            'active_users_growth' => $calculateGrowth($activeUsers, $baseActiveUsers),
            'online_users_growth' => $calculateGrowth($onlineUsers, $baseOnlineUsers),
        ]);
    }
}
