<?php

namespace App\Models;

use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Models\Relations\HasLotteryTicketRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperLotteryTicket
 */
class LotteryTicket extends Model
{
    use HasLotteryTicketRelations;

    protected $fillable = [
        'subscription_id',
        'ticket_number',
        'source_type',
        'source_id'
    ];

    protected $casts = [
        'source_type' => LotteryTicketSourceEnum::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lotteryTicket) {
            if (empty($lotteryTicket->ticket_number)) {
                $lotteryTicket->ticket_number = static::generateUniqueTicketNumber();
            }
        });
    }

    public static function generateUniqueTicketNumber(): string
    {
        do {
            $ticketNumber = (string) random_int(1, 9999);
        } while (static::where('ticket_number', $ticketNumber)->exists());

        return $ticketNumber;
    }

    public static function createForSubscription(Subscription $subscription, LotteryTicketSourceEnum $sourceType, ?int $sourceId = null): self
    {
        return static::create([
            'subscription_id' => $subscription->id,
            'source_type' => $sourceType,
            'source_id' => $sourceId
        ]);
    }

    public function getFormattedTicketNumber(): string
    {
        return str_pad($this->ticket_number, 4, '0', STR_PAD_LEFT);
    }

    public function getSourceLabel(): string
    {
        return $this->source_type->getLabel();
    }

    public function getSourceDescription(): string
    {
        return $this->source_type->getDescription();
    }

    /**
     * Проверить, можно ли изменить номер билета
     */
    public function canChangeNumber(): bool
    {
        // Можно добавить дополнительные проверки, например:
        // - не истек ли срок действия билета
        // - не участвует ли билет в розыгрыше
        return true;
    }

    /**
     * Получить цену за смену номера
     */
    public function getNumberChangePrice(): int
    {
        // Можно сделать динамическую цену в зависимости от номера
        return 50; // 50 рублей
    }

    /**
     * Получить приглашенного пользователя (если билет за реферала)
     */
    public function getReferredUser(): ?Subscription
    {
        if ($this->source_type === LotteryTicketSourceEnum::REFERRAL_BONUS && $this->source_id) {
            return Subscription::find($this->source_id);
        }
        
        return null;
    }

    /**
     * Получить описание источника билета с дополнительной информацией
     */
    public function getDetailedSourceDescription(): string
    {
        if ($this->source_type === LotteryTicketSourceEnum::REFERRAL_BONUS && $this->source_id) {
            $referredUser = $this->getReferredUser();
            if ($referredUser && $referredUser->telegraphChat) {
                $username = $referredUser->telegraphChat->username ? "@{$referredUser->telegraphChat->username}" : "ID: {$referredUser->telegraph_chat_id}";
                return "За приглашение пользователя {$username}";
            }
            return "За приглашение пользователя (ID: {$this->source_id})";
        }
        
        return $this->getSourceDescription();
    }
}
