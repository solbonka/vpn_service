<?php

namespace App\Models;

use App\Enums\Chat\ChatStatusEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\Relations\HasCustomTelegraphChatRelations;
use App\Services\Telegram\TelegramClient;
use DefStudio\Telegraph\Models\TelegraphChat as BaseTelegraphChat;

/**
 * @mixin IdeHelperCustomTelegraphChat
 */
class CustomTelegraphChat extends BaseTelegraphChat
{
    use HasCustomTelegraphChatRelations;

    protected $table = 'telegraph_chats';
    protected $fillable = [
        'client_operating_system_id',
        'first_name',
        'last_name',
        'is_recovery_processed',
    ];

    protected $casts = [
        'is_recovery_processed' => 'boolean',
    ];

    public function getStatus(): ChatStatusEnum
    {
        $subscription = $this->subscriptions()
            ->with(['duration', 'vpnKeys'])
            ->first();

        if ($subscription) {
            $hasVpnKeys = $subscription->vpnKeys->isNotEmpty();
            $isTrial = $subscription->duration?->is_trial ?? false;

            if (!$hasVpnKeys) {
                return ChatStatusEnum::PASSIVE;
            }

            if ($subscription->status === SubscriptionStatusEnum::BLOCKED) {
                return $isTrial ? ChatStatusEnum::BLOCKED_TRIAL : ChatStatusEnum::BLOCKED_PAID;
            } else {
                return ChatStatusEnum::ACTIVE;
            }
        }

        return ChatStatusEnum::NO_SUBSCRIPTION;
    }

    /**
     * Получить отображаемое имя пользователя (только имя)
     */
    public function getDisplayName(): string
    {
        if (!empty($this->first_name)) {
            return $this->first_name;
        }

        return $this->name ?: 'Пользователь';
    }

    /**
     * Обновить информацию о пользователе из Telegram API
     */
    public function updateUserInfoFromTelegram(): void
    {
        try {
            $telegramClient = app(TelegramClient::class);
            $userInfo = $telegramClient->getUserInfo((int) $this->chat_id);

            if ($userInfo) {
                $this->update([
                    'first_name' => $userInfo['first_name'],
                    'last_name' => $userInfo['last_name'],
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update user info from Telegram', [
                'chat_id' => $this->chat_id,
                'error' => $e->getMessage()
            ]);
        }
    }

}
