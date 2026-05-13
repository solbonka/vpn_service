<?php

namespace App\DTO\Actions\Metrics;

class ServerMetricDto
{
    public function __construct(
        public int $serverId,
        public int $totalUsers,
        public int $activeUsers,
        public int $onlineUsers
    ) {}

    public function all(): array
    {
        return [
            'server_id'    => $this->serverId,
            'total_users'  => $this->totalUsers,
            'active_users' => $this->activeUsers,
            'online_users' => $this->onlineUsers
        ];
    }
}
