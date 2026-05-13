<?php

namespace App\Services\PromoCode;

use App\Models\PromoCode;
use App\Models\PromoCodeUsage;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class PromoCodeService
{
    /**
     * Валидировать промокод
     */
    public function validatePromoCode(string $code, int $subscriptionId, ?int $durationId = null): array
    {
        $promoCode = PromoCode::where('code', strtoupper($code))->first();

        if (!$promoCode) {
            return [
                'valid' => false,
                'error' => 'Промокод не найден'
            ];
        }

        if (!$promoCode->is_active) {
            return [
                'valid' => false,
                'error' => 'Промокод неактивен'
            ];
        }

        if ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
            return [
                'valid' => false,
                'error' => 'Срок действия промокода истек'
            ];
        }

        if ($promoCode->usage_limit && $promoCode->used_count >= $promoCode->usage_limit) {
            return [
                'valid' => false,
                'error' => 'Достигнут лимит использования промокода'
            ];
        }

        // Проверяем, не использовал ли уже этот пользователь данный промокод
        $alreadyUsed = PromoCodeUsage::where('promo_code_id', $promoCode->id)
            ->where('subscription_id', $subscriptionId)
            ->exists();

        if ($alreadyUsed) {
            return [
                'valid' => false,
                'error' => 'Вы уже использовали этот промокод'
            ];
        }

        // Проверяем привязку к продолжительностям
        if ($durationId && !$promoCode->isValidForDuration($durationId)) {
            $applicableDurations = $promoCode->getApplicableDurationsNames();
            return [
                'valid' => false,
                'error' => "Данный промокод действует только для тарифов: {$applicableDurations}"
            ];
        }

        return [
            'valid' => true,
            'promo_code' => $promoCode,
            'discount_percent' => $promoCode->discount_percent
        ];
    }

    /**
     * Применить промокод к сумме
     */
    public function applyPromoCode(
        PromoCode $promoCode,
        int $subscriptionId,
        float $originalAmount,
        ?int $paymentId = null
    ): array {
        $discountAmount = ($originalAmount * $promoCode->discount_percent) / 100;
        $finalAmount = $originalAmount - $discountAmount;

        // Сохраняем использование промокода
        $usage = PromoCodeUsage::create([
            'promo_code_id' => $promoCode->id,
            'subscription_id' => $subscriptionId,
            'payment_id' => $paymentId,
            'original_amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
        ]);

        // Увеличиваем счетчик использований
        $promoCode->increment('used_count');

        return [
            'original_amount' => round($originalAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'discount_percent' => $promoCode->discount_percent,
            'final_amount' => round($finalAmount, 2),
            'usage_id' => $usage->id
        ];
    }

    /**
     * Рассчитать сумму со скидкой без применения
     */
    public function calculateDiscount(PromoCode $promoCode, float $originalAmount): array
    {
        $discountAmount = ($originalAmount * $promoCode->discount_percent) / 100;
        $finalAmount = $originalAmount - $discountAmount;

        return [
            'original_amount' => round($originalAmount, 2),
            'discount_amount' => round($discountAmount, 2),
            'discount_percent' => $promoCode->discount_percent,
            'final_amount' => round($finalAmount, 2)
        ];
    }

    /**
     * Отменить использование промокода (например, если платеж не прошел)
     */
    public function cancelPromoCodeUsage(int $usageId): void
    {
        $usage = PromoCodeUsage::find($usageId);
        
        if ($usage) {
            // Уменьшаем счетчик использований
            $usage->promoCode->decrement('used_count');
            
            // Удаляем запись об использовании
            $usage->delete();
        }
    }
}

