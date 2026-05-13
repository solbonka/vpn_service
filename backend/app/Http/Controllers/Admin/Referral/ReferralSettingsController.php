<?php

namespace App\Http\Controllers\Admin\Referral;

use App\Http\Controllers\Controller;
use App\Models\BonusType;
use App\Enums\Referral\ReferralBonusTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReferralSettingsController extends Controller
{
    /**
     * Получить все типы бонусов
     */
    public function index(): JsonResponse
    {
        try {
            $bonusTypes = BonusType::all()->map(function ($bonusType) {
                return [
                    'id' => $bonusType->id,
                    'name' => $bonusType->name,
                    'type' => $bonusType->type->value,
                    'type_label' => $bonusType->type->getLabel(),
                    'amount' => $bonusType->amount,
                    'description' => $bonusType->description,
                    'is_active' => $bonusType->is_active,
                    'formatted_amount' => $bonusType->getFormattedAmount(),
                    'created_at' => $bonusType->created_at,
                    'updated_at' => $bonusType->updated_at,
                ];
            });

            $availableTypes = collect(ReferralBonusTypeEnum::cases())->map(function ($type) {
                return [
                    'value' => $type->value,
                    'label' => $type->getLabel(),
                    'description' => $type->getDescription()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'bonus_types' => $bonusTypes,
                    'available_types' => $availableTypes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting referral settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения настроек реферальной программы'
            ], 500);
        }
    }

    /**
     * Создать новый тип бонуса (запрещено)
     */
    public function store(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Создание новых типов бонусов запрещено. Можно только редактировать существующие 3 типа.'
        ], 403);
    }

    /**
     * Обновить тип бонуса
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $bonusType = BonusType::find($id);

            if (!$bonusType) {
                return response()->json([
                    'success' => false,
                    'error' => 'Тип бонуса не найден'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'amount' => 'sometimes|required|integer|min:1',
                'description' => 'nullable|string|max:500',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Ошибка валидации',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only(['amount', 'description']);
            
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->boolean('is_active');
            }

            $bonusType->update($updateData);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $bonusType->id,
                    'name' => $bonusType->name,
                    'type' => $bonusType->type->value,
                    'type_label' => $bonusType->type->getLabel(),
                    'amount' => $bonusType->amount,
                    'description' => $bonusType->description,
                    'is_active' => $bonusType->is_active,
                    'formatted_amount' => $bonusType->getFormattedAmount(),
                ],
                'message' => 'Тип бонуса успешно обновлен'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating bonus type', [
                'id' => $id,
                'request_data' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка обновления типа бонуса'
            ], 500);
        }
    }

    /**
     * Активировать тип бонуса
     */
    public function activate(int $id): JsonResponse
    {
        try {
            $success = BonusType::activate($id);

            if (!$success) {
                return response()->json([
                    'success' => false,
                    'error' => 'Тип бонуса не найден'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Тип бонуса успешно активирован'
            ]);

        } catch (\Exception $e) {
            Log::error('Error activating bonus type', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка активации типа бонуса'
            ], 500);
        }
    }

    /**
     * Удалить тип бонуса (запрещено)
     */
    public function destroy(int $id): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'Удаление типов бонусов запрещено. Можно только редактировать существующие типы.'
        ], 403);
    }

    /**
     * Получить статистику реферальной программы
     */
    public function stats(): JsonResponse
    {
        try {
            // TODO: Реализовать получение статистики
            $stats = [
                'total_referrals' => 0,
                'active_bonus_type' => null,
                'total_bonus_given' => 0,
                'referral_conversion_rate' => 0
            ];

            $activeBonusType = BonusType::getActive();
            if ($activeBonusType) {
                $stats['active_bonus_type'] = [
                    'id' => $activeBonusType->id,
                    'name' => $activeBonusType->name,
                    'type' => $activeBonusType->type->value,
                    'amount' => $activeBonusType->amount,
                    'formatted_amount' => $activeBonusType->getFormattedAmount()
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting referral stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения статистики'
            ], 500);
        }
    }
}
