<?php

namespace App\DTO\Analytics;

readonly class LotteryStatsDto
{
    public function __construct(
        public int   $totalTickets,
        public array $ticketsBySource,
        public array $topTicketHolders,
    ) {}

    public function toArray(): array
    {
        return [
            'total_tickets' => $this->totalTickets,
            'tickets_by_source' => $this->ticketsBySource,
            'top_ticket_holders' => $this->topTicketHolders,
        ];
    }
}
