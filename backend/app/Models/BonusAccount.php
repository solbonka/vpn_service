<?php

namespace App\Models;

use App\Models\Relations\HasBonusAccountRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBonusAccount
 */
class BonusAccount extends Model
{
    use HasBonusAccountRelations;

    protected $fillable = [
        'subscription_id',
        'balance_rubles',
        'balance_days',
        'balance_lottery_tickets'
    ];

    protected $casts = [
        'balance_rubles' => 'integer',
        'balance_days' => 'integer',
        'balance_lottery_tickets' => 'integer'
    ];

    public static function getOrCreateForSubscription(Subscription $subscription): self
    {
        return static::firstOrCreate(
            ['subscription_id' => $subscription->id],
            [
                'balance_rubles' => 0,
                'balance_days' => 0,
                'balance_lottery_tickets' => 0
            ]
        );
    }

    public function addRubles(int $amount): void
    {
        $this->balance_rubles += $amount;
        $this->save();
    }

    public function addDays(int $days): void
    {
        $this->balance_days += $days;
        $this->save();
    }

    public function addLotteryTickets(int $tickets): void
    {
        $this->balance_lottery_tickets += $tickets;
        $this->save();
    }

    public function spendRubles(int $amount): bool
    {
        if ($this->balance_rubles < $amount) {
            return false;
        }
        
        $this->balance_rubles -= $amount;
        $this->save();
        return true;
    }

    public function spendDays(int $days): bool
    {
        if ($this->balance_days < $days) {
            return false;
        }
        
        $this->balance_days -= $days;
        $this->save();
        return true;
    }

    public function spendLotteryTickets(int $tickets): bool
    {
        if ($this->balance_lottery_tickets < $tickets) {
            return false;
        }
        
        $this->balance_lottery_tickets -= $tickets;
        $this->save();
        return true;
    }

    public function getTotalBalance(): array
    {
        return [
            'rubles' => $this->balance_rubles,
            'days' => $this->balance_days,
            'lottery_tickets' => $this->balance_lottery_tickets
        ];
    }
}
