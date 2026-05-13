<?php

namespace App\Http\Controllers\Admin\Payment;

use App\DTO\Charts\ChartDataRequestDto;
use App\Helpers\Metrics\PaymentMetricHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Charts\ChartDataRequest;
use App\Http\Resources\Metrics\PaymentMetricResource;
use App\Services\Charts\PaymentChartService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentChartService $chartService
    ) {}

    public function getMetrics(): PaymentMetricResource
    {
        return PaymentMetricHelper::aggregate();
    }

    public function getPaymentsChartData(ChartDataRequest $request): JsonResponse
    {
        $requestDto = ChartDataRequestDto::fromRequest($request->validated());
        $response = $this->chartService->getChartData($requestDto);

        return response()->json($response->toArray());
    }
}
