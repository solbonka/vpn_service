<?php

namespace App\Models\Relations;


use App\Models\BonusAccount;
use App\Models\CustomTelegraphChat;
use App\Models\Duration;
use App\Models\LotteryTicket;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\ReferralCode;
use App\Models\RemnawaveVpnKey;
use App\Models\VpnKey;
use App\Models\WebUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasSubscriptionRelations
{
    public function webUser(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }

    public function telegraphChat(): BelongsTo
    {
        return $this->belongsTo(CustomTelegraphChat::class, 'telegraph_chat_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(Duration::class);
    }

    public function vpnKeys(): HasMany
    {
        return $this->hasMany(VpnKey::class);
    }

    public function remnawaveVpnKey(): HasOne
    {
        return $this->hasOne(RemnawaveVpnKey::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function referralCodes(): HasMany
    {
        return $this->hasMany(ReferralCode::class);
    }

    public function referralCode(): HasOne
    {
        return $this->hasOne(ReferralCode::class);
    }

    public function bonusAccount(): HasOne
    {
        return $this->hasOne(BonusAccount::class);
    }

    public function lotteryTickets(): HasMany
    {
        return $this->hasMany(LotteryTicket::class);
    }

    public function referredByCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class, 'referred_by_code_id');
    }
}
