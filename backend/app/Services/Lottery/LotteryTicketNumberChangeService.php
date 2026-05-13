<?php

namespace App\Services\Lottery;

use App\Models\LotteryTicket;
use App\Models\Subscription;
use App\Services\Payment\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LotteryTicketNumberChangeService
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Проверить, свободен ли номер билета
     */
    public function isTicketNumberAvailable(string $ticketNumber): bool
    {
        return !LotteryTicket::where('ticket_number', $ticketNumber)->exists();
    }

    /**
     * Получить доступные номера билетов (не занятые)
     */
    public function getAvailableTicketNumbers(int $limit = 10): array
    {
        $allNumbers = range(1, 9999);
        $takenNumbers = LotteryTicket::pluck('ticket_number')->map(function ($number) {
            return (int) $number;
        })->toArray();

        $availableNumbers = array_diff($allNumbers, $takenNumbers);
        
        return array_slice($availableNumbers, 0, $limit);
    }

    /**
     * Сменить номер билета
     */
    public function changeTicketNumber(
        LotteryTicket $ticket, 
        string $newTicketNumber, 
        Subscription $subscription
    ): bool {
        try {
            // Проверяем, что новый номер свободен
            if (!$this->isTicketNumberAvailable($newTicketNumber)) {
                throw new \Exception("Номер {$newTicketNumber} уже занят");
            }

            // Проверяем, что номер в допустимом диапазоне
            $number = (int) $newTicketNumber;
            if ($number < 1 || $number > 9999) {
                throw new \Exception("Номер должен быть от 1 до 9999");
            }

            // Проверяем, что это не тот же номер
            if ($ticket->ticket_number === $newTicketNumber) {
                throw new \Exception("Этот номер уже установлен для данного билета");
            }

            DB::transaction(function () use ($ticket, $newTicketNumber) {
                $oldNumber = $ticket->ticket_number;
                $ticket->update(['ticket_number' => $newTicketNumber]);
                
                Log::info('Lottery ticket number changed', [
                    'ticket_id' => $ticket->id,
                    'subscription_id' => $ticket->subscription_id,
                    'old_number' => $oldNumber,
                    'new_number' => $newTicketNumber
                ]);
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to change lottery ticket number', [
                'ticket_id' => $ticket->id,
                'new_number' => $newTicketNumber,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Создать платеж за смену номера билета
     */
    public function createPaymentForNumberChange(
        LotteryTicket $ticket,
        string $newTicketNumber,
        Subscription $subscription
    ): array {
        // Цена за смену номера (можно вынести в конфиг)
        $price = 50; // 50 рублей за смену номера

        $paymentData = [
            'subscription_id' => $subscription->id,
            'amount' => $price,
            'description' => "Смена номера лотерейного билета с {$ticket->ticket_number} на {$newTicketNumber}",
            'metadata' => [
                'ticket_id' => $ticket->id,
                'old_number' => $ticket->ticket_number,
                'new_number' => $newTicketNumber,
                'type' => 'ticket_number_change'
            ]
        ];

        return $this->paymentService->createTicketNumberChangePayment($paymentData);
    }

    /**
     * Обработать успешную оплату за смену номера
     */
    public function processSuccessfulPayment(array $paymentData): bool
    {
        try {
            $metadata = $paymentData['metadata'] ?? [];
            
            if (($metadata['type'] ?? '') !== 'ticket_number_change') {
                return false;
            }

            $ticketId = $metadata['ticket_id'] ?? null;
            $newNumber = $metadata['new_number'] ?? null;

            if (!$ticketId || !$newNumber) {
                throw new \Exception('Missing required payment metadata');
            }

            $ticket = LotteryTicket::find($ticketId);
            if (!$ticket) {
                throw new \Exception('Ticket not found');
            }

            $subscription = $ticket->subscription;
            if (!$subscription) {
                throw new \Exception('Subscription not found');
            }

            // Меняем номер билета
            $this->changeTicketNumber($ticket, $newNumber, $subscription);

            Log::info('Lottery ticket number change payment processed successfully', [
                'payment_id' => $paymentData['id'] ?? null,
                'ticket_id' => $ticketId,
                'new_number' => $newNumber
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to process ticket number change payment', [
                'payment_data' => $paymentData,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }
}
