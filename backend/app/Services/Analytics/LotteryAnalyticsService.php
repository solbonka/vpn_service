<?php

namespace App\Services\Analytics;

use App\DTO\Analytics\LotteryStatsDto;
use App\Enums\Lottery\LotteryTicketSourceEnum;
use App\Models\LotteryTicket;
use App\Models\Subscription;

class LotteryAnalyticsService extends BaseAnalyticsService
{
    /**
     * Получить статистику лотерейных билетов
     */
    public function getLotteryTicketStats(): LotteryStatsDto
    {
        $totalTickets = LotteryTicket::count();

        $ticketsBySource = [];
        $ticketsBySourceData = LotteryTicket::selectRaw('source_type, COUNT(*) as count')
            ->groupBy('source_type')
            ->get();

        foreach ($ticketsBySourceData as $item) {
            $sourceType = $item->source_type instanceof LotteryTicketSourceEnum
                ? $item->source_type->value
                : $item->source_type;
            $ticketsBySource[$sourceType] = $item->count;
        }

        $topTicketHolders = LotteryTicket::selectRaw('subscription_id, COUNT(*) as ticket_count')
            ->groupBy('subscription_id')
            ->orderBy('ticket_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($holder) {
                $subscription = Subscription::find($holder->subscription_id);
                return [
                    'subscription_id' => $holder->subscription_id,
                    'telegraph_chat_id' => $subscription?->telegraph_chat_id,
                    'ticket_count' => $holder->ticket_count,
                ];
            })
            ->toArray();

        return new LotteryStatsDto(
            totalTickets: $totalTickets,
            ticketsBySource: $ticketsBySource,
            topTicketHolders: $topTicketHolders,
        );
    }
}
