<?php

namespace App\Services\Lottery;

use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Models\LotteryTicket;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LotteryTicketService
{
    public function createTicketsForSubscription(Subscription $subscription, int $months): int
    {
        $createdTickets = 0;

        try {
            DB::transaction(function () use ($subscription, $months, &$createdTickets) {
                // Создаем билеты за покупку подписки (количество = месяцам подписки)
                // Каждая покупка подписки начисляет билеты независимо от предыдущих покупок
                for ($i = 0; $i < $months; $i++) {
                    LotteryTicket::createForSubscription(
                        $subscription,
                        LotteryTicketSourceEnum::SUBSCRIPTION_PAYMENT,
                        $subscription->id
                    );
                    $createdTickets++;
                }
            });

            Log::info('Lottery tickets created for subscription', [
                'subscription_id' => $subscription->id,
                'tickets_created' => $createdTickets,
                'months' => $months
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create lottery tickets for subscription', [
                'subscription_id' => $subscription->id,
                'months' => $months,
                'error' => $e->getMessage()
            ]);
        }

        return $createdTickets;
    }

    public function createTicketForReferral(Subscription $referrerSubscription, int $referredSubscriptionId): bool
    {
        try {
            LotteryTicket::createForSubscription(
                $referrerSubscription,
                LotteryTicketSourceEnum::REFERRAL_BONUS,
                $referredSubscriptionId
            );

            Log::info('Lottery ticket created for referral', [
                'referrer_subscription_id' => $referrerSubscription->id,
                'referred_subscription_id' => $referredSubscriptionId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to create lottery ticket for referral', [
                'referrer_subscription_id' => $referrerSubscription->id,
                'referred_subscription_id' => $referredSubscriptionId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function getTicketsForSubscription(Subscription $subscription): array
    {
        $tickets = $subscription->lotteryTickets()
            ->orderBy('created_at', 'desc')
            ->get();

        return $tickets->map(function ($ticket) {
            $ticketData = [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'formatted_ticket_number' => $ticket->getFormattedTicketNumber(),
                'source_type' => $ticket->source_type->value,
                'source_label' => $ticket->getSourceLabel(),
                'source_description' => $ticket->getSourceDescription(),
                'detailed_source_description' => $ticket->getDetailedSourceDescription(),
                'created_at' => $ticket->created_at->toISOString(),
            ];

            // Добавляем информацию о приглашенном пользователе, если билет за реферала
            if ($ticket->source_type === \App\Enums\Lottery\LotteryTicketSourceEnum::REFERRAL_BONUS) {
                $referredUser = $ticket->getReferredUser();
                if ($referredUser) {
                    $ticketData['referred_user'] = [
                        'id' => $referredUser->id,
                        'telegraph_chat_id' => $referredUser->telegraph_chat_id,
                        'username' => $referredUser->telegraphChat?->username,
                    ];
                }
            }

            return $ticketData;
        })->toArray();
    }

    public function getTicketStatsForSubscription(Subscription $subscription): array
    {
        $totalTickets = $subscription->lotteryTickets()->count();
        
        $ticketsBySource = $subscription->lotteryTickets()
            ->selectRaw('source_type, COUNT(*) as count')
            ->groupBy('source_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->source_type->value => $item->count];
            });

        return [
            'total_tickets' => $totalTickets,
            'subscription_payment_tickets' => $ticketsBySource->get(LotteryTicketSourceEnum::SUBSCRIPTION_PAYMENT->value, 0),
            'referral_bonus_tickets' => $ticketsBySource->get(LotteryTicketSourceEnum::REFERRAL_BONUS->value, 0),
        ];
    }
}
