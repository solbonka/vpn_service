<?php

namespace App\Http\Controllers\Admin\Subscription;

use App\DTO\Charts\ChartDataRequestDto;
use App\Helpers\Metrics\SubscriptionMetricHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Charts\ChartDataRequest;
use App\Http\Resources\Metrics\SubscriptionMetricResource;
use App\Services\Charts\SubscriptionChartService;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionChartService $chartService
    ) {}

    public function getMetrics(): SubscriptionMetricResource
    {
        return SubscriptionMetricHelper::aggregate();
    }

    public function getSubscriptionsChartData(ChartDataRequest $request): JsonResponse
    {
        $requestDto = ChartDataRequestDto::fromRequest($request->validated());
        $response = $this->chartService->getChartData($requestDto);

        return response()->json($response->toArray());
    }
}
