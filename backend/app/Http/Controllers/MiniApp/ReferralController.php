<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\BonusAccount;
use App\Models\BonusType;
use App\Services\Referral\ReferralProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReferralController extends Controller
{
    public function __construct(
        private ReferralProcessingService $referralProcessingService
    ) {}

    /**
     * Получить информацию о реферальной программе для пользователя
     */
    public function info(Request $request): JsonResponse
    {
        try {
            $subscription = $request->attributes->get('subscription');
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подписка не найдена'
                ], 404);
            }

            // Получаем реферальный код пользователя
            $referralCode = ReferralCode::where('subscription_id', $subscription->id)
                ->where('is_active', true)
                ->first();

            if (!$referralCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Реферальный код не найден'
                ], 404);
            }

            // Получаем бонусный счет
            $bonusAccount = BonusAccount::where('subscription_id', $subscription->id)->first();

            // Получаем активный тип бонуса
            $activeBonusType = BonusType::getActive();

            // Получаем все доступные типы бонусов
            $availableBonusTypes = BonusType::all()->map(function ($bonusType) {
                return [
                    'value' => $bonusType->type->value,
                    'label' => $bonusType->getLabel(),
                    'description' => $bonusType->description
                ];
            });

            // Подсчитываем количество рефералов (пока заглушка)
            $referralCount = 0; // TODO: Реализовать подсчет рефералов

            $data = [
                'referral_code' => $referralCode->code,
                'referral_link' => $referralCode->getReferralLink(),
                'referral_count' => $referralCount,
                'balance' => $bonusAccount ? $bonusAccount->balance_days : 0,
                'balance_in_rubles' => $bonusAccount ? $bonusAccount->balance_rubles : 0,
                'lottery_tickets' => $bonusAccount ? $bonusAccount->balance_lottery_tickets : 0,
                'bonus_amount' => $activeBonusType ? $activeBonusType->amount : 0,
                'available_bonus_types' => $availableBonusTypes,
                'is_program_active' => $activeBonusType !== null,
                'instructions' => $this->getReferralInstructions($activeBonusType),
                'bonus_types' => $this->getBonusTypesInfo($activeBonusType)
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting referral info', [
                'subscription_id' => $subscription->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения информации о реферальной программе'
            ], 500);
        }
    }

    /**
     * Получить статистику рефералов
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $subscription = $request->attributes->get('subscription');
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подписка не найдена'
                ], 404);
            }

            // TODO: Реализовать подсчет статистики рефералов
            $stats = [
                'total_referrals' => 0,
                'successful_referrals' => 0,
                'total_bonus_earned' => 0,
                'current_balance' => 0
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting referral stats', [
                'subscription_id' => $subscription->id ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения статистики'
            ], 500);
        }
    }

    /**
     * Обработать реферальный код (для тестирования)
     */
    public function processReferral(Request $request): JsonResponse
    {
        try {
            $subscription = $request->attributes->get('subscription');
            $referralCode = $request->input('referral_code');

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'error' => 'Подписка не найдена'
                ], 404);
            }

            if (!$referralCode) {
                return response()->json([
                    'success' => false,
                    'error' => 'Реферальный код не указан'
                ], 400);
            }

            $success = $this->referralProcessingService->processReferral($subscription, $referralCode);

            return response()->json([
                'success' => $success,
                'message' => $success 
                    ? 'Реферальный код успешно обработан' 
                    : 'Ошибка обработки реферального кода'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing referral', [
                'subscription_id' => $subscription->id ?? null,
                'referral_code' => $referralCode ?? null,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Ошибка обработки реферального кода'
            ], 500);
        }
    }

    /**
     * Получить инструкции для реферальной программы
     */
    private function getReferralInstructions(?BonusType $activeBonusType): string
    {
        if (!$activeBonusType) {
            return 'Реферальная программа временно недоступна.';
        }

        $bonusInfo = $activeBonusType->getFormattedAmount();
        
        return "🎁 Приглашайте друзей и получайте {$bonusInfo} за каждого!\n\n" .
               "1. Поделитесь реферальной ссылкой с друзьями\n" .
               "2. Друг переходит по ссылке и регистрируется\n" .
               "3. Вы автоматически получаете бонус!\n" .
               "4. Бонусы можно тратить на продление подписки";
    }

    /**
     * Получить информацию о типах бонусов
     */
    private function getBonusTypesInfo(?BonusType $activeBonusType): array
    {
        if (!$activeBonusType) {
            return [];
        }

        return [
            [
                'type' => $activeBonusType->type->value,
                'label' => $activeBonusType->name,
                'description' => $activeBonusType->description
            ]
        ];
    }
}
