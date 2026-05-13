<?php

namespace App\Models;

use App\Models\Relations\HasPromoCodeRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPromoCode
 */
class PromoCode extends Model
{
    use HasPromoCodeRelations;

    protected $fillable = [
        'code',
        'discount_percent',
        'is_active',
        'usage_limit',
        'used_count',
        'expires_at',
    ];

    protected $casts = [
        'discount_percent' => 'integer',
        'is_active' => 'boolean',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    /**
     * Проверить, валиден ли промокод
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Можно ли использовать промокод
     */
    public function canBeUsed(): bool
    {
        return $this->isValid();
    }

    /**
     * Увеличить счетчик использований
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * Scope для активных промокодов
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Рассчитать сумму со скидкой
     */
    public function calculateDiscountedAmount(float $originalAmount): float
    {
        $discountAmount = ($originalAmount * $this->discount_percent) / 100;
        return round($originalAmount - $discountAmount, 2);
    }

    /**
     * Рассчитать размер скидки
     */
    public function calculateDiscountAmount(float $originalAmount): float
    {
        return round(($originalAmount * $this->discount_percent) / 100, 2);
    }

    /**
     */
    public function isValidForDuration(int $durationId): bool
    {
        $hasDurations = $this->durations()->exists();
        
        \Illuminate\Support\Facades\Log::info('PromoCode::isValidForDuration check', [
            'promo_code_id' => $this->id,
            'promo_code' => $this->code,
            'duration_id' => $durationId,
            'has_durations' => $hasDurations,
            'attached_durations' => $this->durations()->pluck('durations.id')->toArray()
        ]);
        
        if (!$hasDurations) {
            return true;
        }

        $isValid = $this->durations()->where('durations.id', $durationId)->exists();
        
        \Illuminate\Support\Facades\Log::info('PromoCode::isValidForDuration result', [
            'is_valid' => $isValid
        ]);
        
        return $isValid;
    }

    /**
     */
    public function appliesToAllDurations(): bool
    {
        return !$this->durations()->exists();
    }

    /**
     */
    public function getApplicableDurationsNames(): string
    {
        if ($this->appliesToAllDurations()) {
            return 'Все тарифы';
        }

        return $this->durations->pluck('name')->join(', ');
    }
}

