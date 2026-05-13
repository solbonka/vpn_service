<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Services\PromoCode\PromoCodeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService
    ) {}

    /**
     * Валидировать промокод
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            Log::info('PromoCodeController::validate - Request received', [
                'request_all' => $request->all(),
                'code' => $request->code,
                'duration_id' => $request->duration_id
            ]);

            $request->validate([
                'code' => 'required|string|max:20',
                'duration_id' => 'nullable|integer|exists:durations,id',
            ]);

            $subscription = $request->get('subscription');
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подписка не найдена'
                ], 404);
            }

            Log::info('PromoCodeController::validate - Calling validatePromoCode', [
                'code' => $request->code,
                'subscription_id' => $subscription->id,
                'duration_id' => $request->duration_id
            ]);

            $result = $this->promoCodeService->validatePromoCode(
                $request->code,
                $subscription->id,
                $request->duration_id
            );

            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 422);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'code' => $result['promo_code']->code,
                    'discount_percent' => $result['discount_percent']
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error validating promo code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка проверки промокода'
            ], 500);
        }
    }

    /**
     * Рассчитать сумму со скидкой
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'code' => 'required|string|max:20',
                'amount' => 'required|numeric|min:1',
                'duration_id' => 'nullable|integer|exists:durations,id',
            ]);

            $subscription = $request->get('subscription');
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подписка не найдена'
                ], 404);
            }

            $validationResult = $this->promoCodeService->validatePromoCode(
                $request->code,
                $subscription->id,
                $request->duration_id
            );

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => $validationResult['error']
                ], 422);
            }

            $calculation = $this->promoCodeService->calculateDiscount(
                $validationResult['promo_code'],
                $request->amount
            );

            return response()->json([
                'success' => true,
                'data' => $calculation
            ]);

        } catch (Exception $e) {
            Log::error('Error calculating promo code discount', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка расчета скидки'
            ], 500);
        }
    }
}

