<?php

namespace App\Models;

use App\Enums\Referral\ReferralBonusTypeEnum;
use App\Models\Relations\HasBonusTypeRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBonusType
 */
class BonusType extends Model
{
    use HasBonusTypeRelations;

    protected $fillable = [
        'name',
        'type',
        'amount',
        'is_active',
        'description'
    ];

    protected $casts = [
        'amount' => 'integer',
        'is_active' => 'boolean',
        'type' => ReferralBonusTypeEnum::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($bonusType) {
            if ($bonusType->is_active && $bonusType->isDirty('is_active')) {
                static::where('id', '!=', $bonusType->id)
                    ->update(['is_active' => false]);
            }
        });

        static::creating(function ($bonusType) {
            if ($bonusType->is_active) {
                static::where('id', '!=', $bonusType->id ?? 0)
                    ->update(['is_active' => false]);
            }
        });
    }

    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    public static function activate(int $id): bool
    {
        $bonusType = static::find($id);
        if (!$bonusType) {
            return false;
        }

        static::where('id', '!=', $id)->update(['is_active' => false]);

        $bonusType->update(['is_active' => true]);

        return true;
    }

    public function getFormattedAmount(): string
    {
        return match($this->type) {
            ReferralBonusTypeEnum::RUBLES => $this->amount . ' руб.',
            ReferralBonusTypeEnum::DAYS => $this->amount . ' дн.',
            ReferralBonusTypeEnum::LOTTERY_TICKETS => $this->amount . ' билетов',
        };
    }

    public function getLabel(): string
    {
        return $this->type->getLabel();
    }
}
