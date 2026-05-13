<?php

namespace App\Models;

use App\Enums\Chat\ChatStatusEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Relations\HasSubscriptionRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @mixin IdeHelperSubscription
 */
class Subscription extends Model
{
    use HasSubscriptionRelations;

    protected $fillable = [
        'token',
        'telegraph_chat_id',
        'referred_by_code_id',
        'plan_id',
        'duration_id',
        'status',
        'end_datetime'
    ];

    protected $casts = [
        'status' => SubscriptionStatusEnum::class,
        'end_datetime' => 'datetime'
    ];

    public function getEndDatetimeSeconds(): int
    {
        return $this->end_datetime->getTimestamp();
    }

    public function hasActiveVpnKey(): bool
    {
       return $this->vpnKeys()->where('is_active', true)->exists();
    }

    /**
     * @return Collection<int, Server>
     */
    public function activeServers(): Collection
    {
        return $this->plan->servers()
            ->where('is_active', true)
            ->get();
    }

    public function activeVpnKeys()
    {
        return $this->vpnKeys()
            ->where('is_active', true)
            ->with('server')
            ->get()
            ->sortBy('server.order')
            ->values();
    }

    public static function findExpired(): Builder
    {
        return static::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('end_datetime', '<', now());
    }

    public static function findExpiring(): Builder
    {
        return static::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->whereDate('end_datetime', Carbon::tomorrow()->toDateString());
    }

    public static function findPassive(): Builder
    {
        return static::query()
            ->where('status', SubscriptionStatusEnum::ACTIVE)
            ->where('created_at', '<=', now()->subHours(3))
            ->whereDoesntHave('vpnKeys')
            ->whereDoesntHave('telegraphChat.chatNotifications', function ($query) {
                $query->where('notification_type', ChatStatusEnum::PASSIVE->value);
            })
            ->with('telegraphChat');
    }

    public static function findPassiveForNotifications(): Builder
    {
        $startDate = config('telegram.notification_system_start_date');
        $systemStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfDay();

        return static::findPassive()
            ->where('created_at', '>=', $systemStartDate);
    }

    public static function findBlocked(?bool $isTrial = null): Builder
    {
        $query = static::query()
            ->where('status', SubscriptionStatusEnum::BLOCKED)
            ->where('end_datetime', '<', now()->subHours(24))
            ->whereHas('vpnKeys')
            ->with(['telegraphChat', 'duration']);

        if ($isTrial !== null) {
            $query->whereHas('duration', function ($q) use ($isTrial) {
                $q->where('is_trial', $isTrial);
            });
        }

        return $query;
    }

    public static function findBlockedForNotifications(?bool $isTrial = null): Builder
    {
        $startDate = config('telegram.notification_system_start_date');
        $systemStartDate = $startDate ? Carbon::parse($startDate)->startOfDay() : now()->startOfDay();

        return static::findBlocked($isTrial)
            ->whereDoesntHave('telegraphChat.chatNotifications', function ($query) use ($isTrial) {
                $query->where(
                    'notification_type',
                    $isTrial ? ChatStatusEnum::BLOCKED_TRIAL->value : ChatStatusEnum::BLOCKED_PAID->value
                );
            })
            ->where('end_datetime', '>=', $systemStartDate);
    }
}
