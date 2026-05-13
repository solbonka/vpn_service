<?php

namespace App\Models;

use App\Models\Relations\HasReferralCodeRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperReferralCode
 */
class ReferralCode extends Model
{
    use HasReferralCodeRelations;

    protected $fillable = [
        'subscription_id',
        'code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($referralCode) {
            if (empty($referralCode->code)) {
                $referralCode->code = static::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function getReferralLink(): string
    {
        $botLink = env('TELEGRAM_BOT_LINK', 't.me/your_bot_link');
        
        // Убираем https:// если он уже есть в botLink
        if (str_starts_with($botLink, 'https://')) {
            return "{$botLink}?start=ref_{$this->code}";
        }
        
        return "https://{$botLink}?start=ref_{$this->code}";
    }

    public static function getOrCreateForSubscription(Subscription $subscription): self
    {
        return static::firstOrCreate(
            ['subscription_id' => $subscription->id],
            [
                'is_active' => true
            ]
        );
    }

}
