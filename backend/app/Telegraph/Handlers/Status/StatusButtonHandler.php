<?php

namespace App\Telegraph\Handlers\Status;

use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Models\ClientOperatingSystem;
use App\Models\Subscription;
use App\Telegraph\Handlers\BaseMessageHandler;
use Illuminate\Support\Carbon;

class StatusButtonHandler extends BaseMessageHandler
{
    private string $domain;

    protected function initialize(): void
    {
        $this->domain = config('telegram.domain');
    }

    public function canHandle(string $message): bool
    {
        return $message === 'ℹ️ Статус';

    }

    public function handle(string $message): void
    {
        $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

        if (
            $subscription &&
            $subscription->status === SubscriptionStatusEnum::ACTIVE &&
            $subscription->hasActiveVpnKey()
        ) {
            $endDate = Carbon::parse($subscription->end_datetime);
            $formattedEndDate = $endDate->format('d.m.Y');
            $daysLeft = max(0, Carbon::now()->diffInDays($endDate, false));

            $timestamp = time();

            $relativePath = route('subscription.keys', ['subscription' => $subscription->token], false);
            $subscriptionUrl = $this->domain . $relativePath . "?t={$timestamp}";

            $appLink = $subscriptionUrl .
                "#" . config('app.name') . "[" . $this->chat->id . "]";

            $message = StatusMessage::messageSubscription(
                $formattedEndDate,
                $daysLeft,
                $appLink
            );
        } else {
            $message = StatusMessage::messageNotSubscription($subscription);
        }

        $this->chat->message($message)
            ->markdown()
            ->send();
    }
}
