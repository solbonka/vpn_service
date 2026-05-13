<?php

namespace App\Actions\Metrics;

use App\DTO\Actions\Metrics\ServerMetricDto;
use App\Models\ServerMetric;

class ServerMetricAction
{
    public function execute(ServerMetricDto $dto)
    {
        return ServerMetric::create($dto->all());
    }
}
