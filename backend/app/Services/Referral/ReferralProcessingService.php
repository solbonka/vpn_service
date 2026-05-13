<?php

namespace App\Services\Referral;

use App\Enums\Referral\ReferralBonusTypeEnum;
use App\Models\BonusAccount;
use App\Models\BonusType;
use App\Models\ReferralCode;
use App\Models\Subscription;
use App\Services\Lottery\LotteryTicketService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralProcessingService
{
    public function __construct(
        private LotteryTicketService $lotteryTicketService
    ) {}

    public function processReferral(Subscription $newSubscription, string $referralCode): bool
    {
        try {
            $referralCodeModel = ReferralCode::where('code', $referralCode)
                ->where('is_active', true)
                ->with('subscription')
                ->first();

            if (!$referralCodeModel) {
                Log::warning('Referral code not found or inactive', [
                    'code' => $referralCode,
                    'new_subscription_id' => $newSubscription->id
                ]);
                return false;
            }

            $referrerSubscription = $referralCodeModel->subscription;

            // Проверяем, что пользователь не приглашает сам себя
            if ($referrerSubscription->telegraph_chat_id === $newSubscription->telegraph_chat_id) {
                Log::warning('User tried to refer themselves', [
                    'subscription_id' => $newSubscription->id,
                    'referrer_subscription_id' => $referrerSubscription->id,
                    'referral_code' => $referralCode
                ]);
                return false;
            }

            // Получаем активный тип бонуса
            $bonusType = BonusType::getActive();
            if (!$bonusType) {
                Log::warning('No active bonus type found', [
                    'code' => $referralCode
                ]);
                return false;
            }

            return DB::transaction(function () use ($referrerSubscription, $newSubscription, $referralCodeModel, $bonusType) {
                // Получаем или создаем бонусный счет пригласившего
                $bonusAccount = BonusAccount::getOrCreateForSubscription($referrerSubscription);

                // Начисляем бонус в зависимости от активного типа
                $amount = $bonusType->amount;

                switch ($bonusType->type) {
                    case ReferralBonusTypeEnum::RUBLES:
                        $bonusAccount->addRubles($amount);
                        break;

                    case ReferralBonusTypeEnum::DAYS:
                        $bonusAccount->addDays($amount);
                        break;

                    case ReferralBonusTypeEnum::LOTTERY_TICKETS:
                        $bonusAccount->addLotteryTickets($amount);

                        // Создаем лотерейные билеты за реферала
                        for ($i = 0; $i < $amount; $i++) {
                            $this->lotteryTicketService->createTicketForReferral(
                                $referrerSubscription,
                                $newSubscription->id // ID приглашенного пользователя
                            );
                        }
                        break;
                }

                Log::info('Referral processed successfully', [
                    'referrer_subscription_id' => $referrerSubscription->id,
                    'referred_subscription_id' => $newSubscription->id,
                    'referral_code' => $referralCodeModel->code,
                    'bonus_type' => $bonusType->name,
                    'bonus_amount' => $amount,
                    'bonus_account_balance' => $bonusAccount->getTotalBalance()
                ]);

                return true;
            });

        } catch (\Exception $e) {
            Log::error('Error processing referral', [
                'referral_code' => $referralCode,
                'new_subscription_id' => $newSubscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    public function extractReferralCodeFromStartCommand(string $startCommand): ?string
    {
        // Обрабатываем команды вида: /start ref_ABC12345
        if (preg_match('/^\/start\s+ref_([A-Z0-9]{8})$/', $startCommand, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
