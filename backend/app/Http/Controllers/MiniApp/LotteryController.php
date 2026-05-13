<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Models\MiniappSettings;
use App\Models\LotteryTicket;
use App\Services\Lottery\LotteryTicketService;
use App\Services\Lottery\LotteryTicketNumberChangeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LotteryController extends Controller
{
    public function __construct(
        private LotteryTicketService $lotteryTicketService,
        private LotteryTicketNumberChangeService $numberChangeService
    ) {}

    public function info(Request $request): JsonResponse
    {
        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'error' => 'Подписка не найдена'
            ], 404);
        }

        $stats = $this->lotteryTicketService->getTicketStatsForSubscription($subscription);
        $lotteryImage = MiniappSettings::getLotteryImage();

        return response()->json([
            'success' => true,
            'data' => [
                'lottery_info' => [
                    'title' => 'Розыгрыш iPhone',
                    'description' => 'Участвуйте в розыгрыше и выигрывайте призы!',
                    'prize' => 'iPhone',
                    'prize_image' => $lotteryImage,
                    'draw_date' => '2024-12-31',
                    'rules' => [
                        'За каждую оплаченную подписку вы получаете лотерейные билеты',
                        'Количество билетов равно количеству месяцев подписки',
                        'За каждого приглашенного друга, который оплатил подписку, вы получаете дополнительный билет',
                        'Чем больше билетов, тем выше шанс на победу!'
                    ]
                ],
                'ticket_stats' => $stats
            ]
        ]);
    }

    public function tickets(Request $request): JsonResponse
    {
        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'error' => 'Подписка не найдена'
            ], 404);
        }

        $tickets = $this->lotteryTicketService->getTicketsForSubscription($subscription);

        return response()->json([
            'success' => true,
            'data' => [
                'tickets' => $tickets
            ]
        ]);
    }

    /**
     * Проверить доступность номера билета
     */
    public function checkNumberAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'number' => 'required|string|regex:/^\d{1,4}$/'
        ]);

        $number = $request->input('number');
        $isAvailable = $this->numberChangeService->isTicketNumberAvailable($number);

        return response()->json([
            'success' => true,
            'data' => [
                'number' => $number,
                'available' => $isAvailable
            ]
        ]);
    }

    /**
     * Получить доступные номера билетов
     */
    public function getAvailableNumbers(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $availableNumbers = $this->numberChangeService->getAvailableTicketNumbers($limit);

        return response()->json([
            'success' => true,
            'data' => [
                'available_numbers' => $availableNumbers
            ]
        ]);
    }

    /**
     * Получить результативную таблицу всех билетов
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $currentSubscription = $request->attributes->get('subscription');
        
        $tickets = LotteryTicket::with(['subscription.telegraphChat'])
            ->orderBy('ticket_number', 'asc')
            ->get();

        $leaderboardData = $tickets->map(function ($ticket) use ($currentSubscription) {
            $isCurrentUser = $currentSubscription && $ticket->subscription_id === $currentSubscription->id;
            
            // Используем новый метод getDisplayName() если доступен, иначе fallback на старое поле
            $ownerName = 'Неизвестный пользователь';
            if ($ticket->subscription->telegraphChat) {
                $ownerName = $ticket->subscription->telegraphChat->getDisplayName();
            }
            
            return [
                'ticket_number' => $ticket->ticket_number,
                'formatted_ticket_number' => $ticket->getFormattedTicketNumber(),
                'owner_name' => $ownerName,
                'is_current_user' => $isCurrentUser,
                'created_at' => $ticket->created_at->toISOString(),
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'leaderboard' => $leaderboardData,
                'total_tickets' => count($leaderboardData)
            ]
        ]);
    }

    /**
     * Создать платеж за смену номера билета
     */
    public function createNumberChangePayment(Request $request): JsonResponse
    {
        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'error' => 'Подписка не найдена'
            ], 404);
        }

        $request->validate([
            'ticket_id' => 'required|integer|exists:lottery_tickets,id',
            'new_number' => 'required|string|regex:/^\d{1,4}$/'
        ]);

        $ticketId = $request->input('ticket_id');
        $newNumber = $request->input('new_number');

        // Проверяем, что билет принадлежит пользователю
        $ticket = LotteryTicket::where('id', $ticketId)
            ->where('subscription_id', $subscription->id)
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'error' => 'Билет не найден или не принадлежит вам'
            ], 404);
        }

        // Проверяем, можно ли изменить номер
        if (!$ticket->canChangeNumber()) {
            return response()->json([
                'success' => false,
                'error' => 'Номер этого билета нельзя изменить'
            ], 400);
        }

        // Проверяем доступность нового номера
        if (!$this->numberChangeService->isTicketNumberAvailable($newNumber)) {
            return response()->json([
                'success' => false,
                'error' => 'Номер уже занят'
            ], 400);
        }

        try {
            $paymentData = $this->numberChangeService->createPaymentForNumberChange(
                $ticket,
                $newNumber,
                $subscription
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'payment' => $paymentData,
                    'price' => $ticket->getNumberChangePrice()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка создания платежа: ' . $e->getMessage()
            ], 500);
        }
    }
}
