<?php

namespace App\Http\Controllers\Admin\PromoCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCode\StorePromoCodeRequest;
use App\Http\Requests\PromoCode\UpdatePromoCodeRequest;
use App\Models\Duration;
use App\Models\PromoCode;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    /**
     * Получить список всех промокодов
     */
    public function index(): JsonResponse
    {
        try {
            $promoCodes = PromoCode::query()
                ->with('durations')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($promoCode) {
                    return [
                        'id' => $promoCode->id,
                        'code' => $promoCode->code,
                        'discount_percent' => $promoCode->discount_percent,
                        'is_active' => $promoCode->is_active,
                        'usage_limit' => $promoCode->usage_limit,
                        'used_count' => $promoCode->used_count,
                        'expires_at' => $promoCode->expires_at,
                        'is_valid' => $promoCode->isValid(),
                        'durations' => $promoCode->durations->map(fn($duration) => [
                            'id' => $duration->id,
                            'name' => $duration->name
                        ]),
                        'applicable_durations' => $promoCode->getApplicableDurationsNames(),
                        'created_at' => $promoCode->created_at,
                        'updated_at' => $promoCode->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $promoCodes
            ]);

        } catch (Exception $e) {
            Log::error('Error getting promo codes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения промокодов'
            ], 500);
        }
    }

    /**
     * Создать новый промокод
     */
    public function store(StorePromoCodeRequest $request): JsonResponse
    {
        try {
            $promoCode = PromoCode::create([
                'code' => $request->code,
                'discount_percent' => $request->discount_percent,
                'is_active' => $request->boolean('is_active', true),
                'usage_limit' => $request->usage_limit,
                'expires_at' => $request->expires_at,
            ]);

            // Привязываем продолжительности
            if ($request->has('duration_ids')) {
                $promoCode->durations()->sync($request->duration_ids);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'discount_percent' => $promoCode->discount_percent,
                    'is_active' => $promoCode->is_active,
                    'usage_limit' => $promoCode->usage_limit,
                    'used_count' => $promoCode->used_count,
                    'expires_at' => $promoCode->expires_at,
                    'is_valid' => $promoCode->isValid(),
                    'created_at' => $promoCode->created_at,
                ],
                'message' => 'Промокод успешно создан'
            ], 201);

        } catch (Exception $e) {
            Log::error('Error creating promo code', [
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка создания промокода'
            ], 500);
        }
    }

    /**
     * Получить детали промокода
     */
    public function show(int $id): JsonResponse
    {
        try {
            $promoCode = PromoCode::with('durations')->find($id);

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Промокод не найден'
                ], 404);
            }

            $paymentsCount = $promoCode->payments()->count();
            $paymentsTotal = $promoCode->payments()
                ->where('status', 'succeeded')
                ->sum('amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'discount_percent' => $promoCode->discount_percent,
                    'is_active' => $promoCode->is_active,
                    'usage_limit' => $promoCode->usage_limit,
                    'used_count' => $promoCode->used_count,
                    'expires_at' => $promoCode->expires_at,
                    'is_valid' => $promoCode->isValid(),
                    'durations' => $promoCode->durations->map(fn($duration) => [
                        'id' => $duration->id,
                        'name' => $duration->name
                    ]),
                    'applicable_durations' => $promoCode->getApplicableDurationsNames(),
                    'created_at' => $promoCode->created_at,
                    'updated_at' => $promoCode->updated_at,
                    'payments_count' => $paymentsCount,
                    'payments_total' => $paymentsTotal,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting promo code', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения промокода'
            ], 500);
        }
    }

    /**
     * Обновить промокод
     */
    public function update(UpdatePromoCodeRequest $request, int $id): JsonResponse
    {
        try {
            $promoCode = PromoCode::find($id);

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Промокод не найден'
                ], 404);
            }

            $updateData = [];

            if ($request->has('code')) {
                $updateData['code'] = $request->code;
            }

            if ($request->has('discount_percent')) {
                $updateData['discount_percent'] = $request->discount_percent;
            }

            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            if ($request->has('usage_limit')) {
                $updateData['usage_limit'] = $request->usage_limit;
            }

            if ($request->has('expires_at')) {
                $updateData['expires_at'] = $request->expires_at;
            }

            $promoCode->update($updateData);

            // Обновляем привязку продолжительностей
            if ($request->has('duration_ids')) {
                $promoCode->durations()->sync($request->duration_ids);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $promoCode->id,
                    'code' => $promoCode->code,
                    'discount_percent' => $promoCode->discount_percent,
                    'is_active' => $promoCode->is_active,
                    'usage_limit' => $promoCode->usage_limit,
                    'used_count' => $promoCode->used_count,
                    'expires_at' => $promoCode->expires_at,
                    'is_valid' => $promoCode->isValid(),
                    'updated_at' => $promoCode->updated_at,
                ],
                'message' => 'Промокод успешно обновлен'
            ]);

        } catch (Exception $e) {
            Log::error('Error updating promo code', [
                'id' => $id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка обновления промокода'
            ], 500);
        }
    }

    /**
     * Удалить промокод
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $promoCode = PromoCode::find($id);

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Промокод не найден'
                ], 404);
            }

            // Проверяем, использовался ли промокод
            $hasPayments = $promoCode->payments()->exists();

            if ($hasPayments) {
                return response()->json([
                    'success' => false,
                    'error' => 'Невозможно удалить промокод, который уже использовался в платежах. Вы можете деактивировать его.'
                ], 422);
            }

            $promoCode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Промокод успешно удален'
            ]);

        } catch (Exception $e) {
            Log::error('Error deleting promo code', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка удаления промокода'
            ], 500);
        }
    }

    /**
     * Получить список доступных продолжительностей (не trial)
     */
    public function getAvailableDurations(): JsonResponse
    {
        try {
            $durations = Duration::query()
                ->where('is_trial', false)
                ->orderBy('days')
                ->get()
                ->map(function ($duration) {
                    return [
                        'id' => $duration->id,
                        'name' => $duration->name,
                        'days' => $duration->days,
                        'discount_percentage' => $duration->discount_percentage
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $durations
            ]);

        } catch (Exception $e) {
            Log::error('Error getting available durations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения списка тарифов'
            ], 500);
        }
    }
}

