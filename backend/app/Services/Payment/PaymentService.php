<?php

namespace App\Services\Payment;

use App\Actions\Payment\StorePaymentAction;
use App\DTO\Actions\Payment\StorePaymentActionDto;
use App\DTO\Payment\PaymentCreationData;
use App\Enums\Payment\PaymentStatusEnum;
use App\Helpers\MenuKeyboardHelper;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use App\Services\Telegram\ErrorNotificationService;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use YooKassa\Client;
use YooKassa\Common\Exceptions\ApiConnectionException;
use YooKassa\Common\Exceptions\ApiException;
use YooKassa\Common\Exceptions\AuthorizeException;
use YooKassa\Common\Exceptions\BadApiRequestException;
use YooKassa\Common\Exceptions\ExtensionNotFoundException;
use YooKassa\Common\Exceptions\ForbiddenException;
use YooKassa\Common\Exceptions\InternalServerError;
use YooKassa\Common\Exceptions\NotFoundException;
use YooKassa\Common\Exceptions\ResponseProcessingException;
use YooKassa\Common\Exceptions\TooManyRequestsException;
use YooKassa\Common\Exceptions\UnauthorizedException;
use YooKassa\Model\Payment\PaymentInterface;

class PaymentService
{
    use ErrorMessageTrait;

    private Client $client;
    private string $returnUrl;
    private ErrorNotificationService $errorNotificationService;

    public function __construct(Client $client, ErrorNotificationService $errorNotificationService)
    {
        $this->client = $client;
        $this->returnUrl = config('payment.yookassa.return_url');
        $this->errorNotificationService = $errorNotificationService;
    }

    /**
     * @throws NotFoundException
     * @throws ApiException
     * @throws ResponseProcessingException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws AuthorizeException
     * @throws InternalServerError
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     * @throws ApiConnectionException
     * @throws UnauthorizedException
     */
    public function createPayment(PaymentCreationData $paymentData): array
    {
        $payment = $this->client->createPayment([
            'amount' => [
                'value' => $paymentData->price,
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $this->returnUrl,
            ],
            'capture' => true,
            'description' => 'Оплата по тарифу: ' . $paymentData->plan->name . ' сроком на ' . $paymentData->duration->name,
            'metadata' => array_filter(array_merge([
                'plan_id' => $paymentData->plan->id,
                'duration_id' => $paymentData->duration->id,
                'chat_id' => $paymentData->chatId,
            ], $paymentData->metadata), fn ($value) => $value !== null)
        ], uniqid('', true));

        $this->storePaymentToDataBase($payment);

        $paymentId = $payment->getId();
        $paymentUrl = $payment->getConfirmation()->getConfirmationUrl();

        return [
            'id' => $paymentId,
            'payment_url' => $paymentUrl
        ];
    }

    /**
     * Создать платеж за смену номера билета
     */
    public function createTicketNumberChangePayment(array $paymentData): array
    {
        $payment = $this->client->createPayment([
            'amount' => [
                'value' => $paymentData['amount'],
                'currency' => 'RUB',
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => $this->returnUrl,
            ],
            'capture' => true,
            'description' => $paymentData['description'],
            'metadata' => $paymentData['metadata']
        ], uniqid('', true));

        $this->storeTicketNumberChangePaymentToDatabase($payment, $paymentData);

        $paymentId = $payment->getId();
        $paymentUrl = $payment->getConfirmation()->getConfirmationUrl();

        return [
            'id' => $paymentId,
            'payment_url' => $paymentUrl
        ];
    }

    /**
     * @throws NotFoundException
     * @throws ApiException
     * @throws ResponseProcessingException
     * @throws BadApiRequestException
     * @throws ExtensionNotFoundException
     * @throws Throwable
     * @throws InternalServerError
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function callbackPayment(PaymentInterface $payment): void
    {
        Log::info('Payment webhook processing started', [
            'payment_id' => $payment->getId(),
            'payment_status' => $payment->status,
            'payment_paid' => $payment->paid,
            'payment_amount' => $payment->amount->getValue(),
            'payment_created_at' => $payment->created_at ?? null,
            'webhook_received_at' => now(),
            'processing_started_at' => now()
        ]);

        $chatId = $payment->metadata['chat_id'] ?? null;
        $planId = $payment->metadata['plan_id'] ?? null;
        $durationId = $payment->metadata['duration_id'] ?? null;
        $paymentType = $payment->metadata['type'] ?? 'subscription';
        $paymentSource = $payment->metadata['source'] ?? 'telegram';


        if ($paymentSource === 'web') {
            $paymentDataBase = Payment::query()->where('yookassa_payment_id', $payment->getId())->first();
            if (!$paymentDataBase) {
                throw new RuntimeException("Не найден платеж в БД: {$payment->getId()}");
            }

            if ($payment->status === 'succeeded' && $payment->paid) {
                if ($paymentDataBase->status !== PaymentStatusEnum::SUCCEEDED) {
                    $paymentDataBase->status = PaymentStatusEnum::SUCCEEDED;
                    $paymentDataBase->save();

                    $subscription = $paymentDataBase->subscription;
                    if ($subscription) {
                        $subscription->plan_id = (int) $planId;
                        $subscription->duration_id = (int) $durationId;
                        $subscription->status = \App\Enums\Subscription\SubscriptionStatusEnum::ACTIVE;
                        $subscription->end_datetime = now()->addDays((int) optional($subscription->duration)->days ?? 30);
                        $subscription->save();
                    }
                }
                return;
            }

            if ($payment->status === 'canceled') {
                $paymentDataBase->status = PaymentStatusEnum::CANCELED;
                $paymentDataBase->save();
                return;
            }

            return;
        }

        if ($paymentType === 'ticket_number_change') {
            $this->handleTicketNumberChangePayment($payment);
            return;
        }

        Log::info('Payment metadata extracted', [
            'payment_id' => $payment->getId(),
            'chat_id' => $chatId,
            'plan_id' => $planId,
            'duration_id' => $durationId,
            'metadata' => $payment->metadata
        ]);

        if (!$chatId || !$planId || !$durationId) {
            Log::error('Missing payment metadata', [
                'payment_id' => $payment->getId(),
                'chat_id' => $chatId,
                'plan_id' => $planId,
                'duration_id' => $durationId
            ]);
            throw new RuntimeException(
                'Отсутствуют ключевые метаданные в платеже: ' . json_encode(compact('chatId', 'planId', 'durationId'))
            );
        }

        $subscription = Subscription::query()->where('telegraph_chat_id', $chatId)->first();
        $chat = TelegraphChat::query()->find($subscription?->telegraph_chat_id);

        Log::info('Subscription and chat lookup', [
            'payment_id' => $payment->getId(),
            'chat_id' => $chatId,
            'subscription_id' => $subscription?->id,
            'subscription_status' => $subscription?->status?->value,
            'subscription_end_datetime' => $subscription?->end_datetime,
            'subscription_plan_id' => $subscription?->plan_id,
            'subscription_duration_id' => $subscription?->duration_id,
            'chat_found' => $chat ? true : false
        ]);

        if (!$subscription || !$chat) {
            Log::error('Subscription or chat not found', [
                'payment_id' => $payment->getId(),
                'chat_id' => $chatId,
                'subscription_found' => $subscription ? true : false,
                'chat_found' => $chat ? true : false
            ]);
            throw new RuntimeException("Не найден subscription или chat для chat_id: $chatId");
        }

        $paymentDataBase = Payment::query()->where('yookassa_payment_id', $payment->getId())->first();

        Log::info('Payment database record lookup', [
            'payment_id' => $payment->getId(),
            'payment_db_found' => $paymentDataBase ? true : false,
            'payment_db_status' => $paymentDataBase?->status?->value,
            'payment_db_created_at' => $paymentDataBase?->created_at
        ]);

        try {
            if ($payment->status === 'succeeded' && $payment->paid) {
                Log::info('Processing succeeded payment', [
                    'payment_id' => $payment->getId(),
                    'payment_status' => $payment->status,
                    'payment_paid' => $payment->paid,
                    'payment_db_status' => $paymentDataBase?->status?->value
                ]);

                if ($paymentDataBase->status === PaymentStatusEnum::SUCCEEDED) {
                    Log::info("Платеж уже был обработан ранее", [
                        'payment_id' => $payment->getId(),
                        'chat_id' => $chatId,
                        'subscription_id' => $subscription->id,
                        'subscription_status' => $subscription->status->value
                    ]);

                    $this->syncVpnKeys($subscription, $chat);

                    return;
                }

                Log::info('Updating payment status to succeeded', [
                    'payment_id' => $payment->getId(),
                    'old_status' => $paymentDataBase?->status?->value,
                    'new_status' => PaymentStatusEnum::SUCCEEDED->value
                ]);

                $paymentDataBase->status = PaymentStatusEnum::SUCCEEDED;
                $paymentDataBase->save();

                $this->deletePreviousPaymentMessage($payment->id, $chat);

                Log::info('Starting subscription update', [
                    'payment_id' => $payment->getId(),
                    'subscription_id' => $subscription->id,
                    'current_subscription_status' => $subscription->status->value,
                    'current_subscription_plan_id' => $subscription->plan_id,
                    'current_subscription_duration_id' => $subscription->duration_id,
                    'current_subscription_end_datetime' => $subscription->end_datetime,
                    'new_plan_id' => $planId,
                    'new_duration_id' => $durationId
                ]);

                $this->updateSubscription($subscription, $planId, $durationId, $chat);

                $subscription->refresh();

                Log::info('Subscription updated, refreshing from database', [
                    'payment_id' => $payment->getId(),
                    'subscription_id' => $subscription->id,
                    'new_subscription_status' => $subscription->status->value,
                    'new_subscription_plan_id' => $subscription->plan_id,
                    'new_subscription_duration_id' => $subscription->duration_id,
                    'new_subscription_end_datetime' => $subscription->end_datetime
                ]);

                $this->sendSuccessMessage($chat, $payment);

                Log::info("Платеж обработан успешно", [
                    'payment_id' => $payment->getId(),
                    'chat_id' => $chatId,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $planId,
                    'duration_id' => $durationId,
                    'final_subscription_status' => $subscription->status->value,
                ]);

                $this->syncVpnKeys($subscription, $chat);
            } elseif ($payment->status === 'canceled') {
                $paymentDataBase->status = PaymentStatusEnum::CANCELED;
                $paymentDataBase->save();

                Log::info("Платеж отменен", [
                    'chatId' => $chatId,
                    'cancellation_details' => $payment->getCancellationDetails() ?? 'Не указано'
                ]);
            } elseif ($payment->status === 'waiting_for_capture') {
                $captureResponse = $this->client->capturePayment([
                    'amount' => $payment->amount
                ], $payment->getId(), uniqid('', true));

                Log::info("Платеж отправлен на capture", [
                    'payment_id' => $payment->getId(),
                    'chat_id' => $chatId,
                    'capture_id' => $captureResponse->id ?? 'unknown'
                ]);
            } else {
                throw new RuntimeException(
                    "Платеж не прошел: статус=$payment->status, paid=" .
                    ($payment->paid ? 'true' : 'false')
                );
            }
        } catch (Throwable $e) {
            Log::error('Сбой в обработке платежа', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_id' => $payment->id,
                'chat_id' => $chatId
            ]);

            $this->errorNotificationService->sendErrorNotification($e, [
                'service' => 'PaymentService',
                'method' => 'processPayment',
                'payment_id' => $payment->id,
                'chat_id' => $chatId,
                'payment_status' => $payment->status,
                'amount' => $payment->amount->getValue(),
                'currency' => $payment->amount->getCurrency(),
            ]);

            if ($paymentDataBase->status !== PaymentStatusEnum::SUCCEEDED) {
                $paymentDataBase->status = PaymentStatusEnum::FAILED;
                $paymentDataBase->save();

                $chat->message($this->getErrorMessage())->send();
            }

            throw $e;
        }
    }

    private function deletePreviousPaymentMessage(string $paymentId, TelegraphChat $chat): void
    {
        if ($cache = Cache::get("payment-message:$paymentId")) {
            $chat->deleteMessage($cache['message_id'])->send();
            Cache::forget("payment-message:$paymentId");
        }
    }

    private function sendSuccessMessage(TelegraphChat $chat, PaymentInterface $payment): void
    {
        $message = "
✅ Платеж на сумму {$payment->amount->getValue()} руб. успешно подтвержден!
Ваша подписка активирована.
";

        MenuKeyboardHelper::send($chat, $message);
    }

    private function updateSubscription(
        Subscription  $subscription,
        int           $planId,
        int           $durationId,
        TelegraphChat $chat
    ): void
    {
        Log::info('Calling SubscriptionService::update', [
            'subscription_id' => $subscription->id,
            'plan_id' => $planId,
            'duration_id' => $durationId,
            'chat_id' => $chat->id
        ]);

        $subscriptionUpdated = app(SubscriptionService::class, ['chat' => $chat])
            ->update($subscription, $planId, $durationId);

        Log::info('SubscriptionService::update result', [
            'subscription_id' => $subscription->id,
            'update_result' => $subscriptionUpdated ? 'success' : 'failed',
            'updated_subscription_id' => $subscriptionUpdated?->id,
            'updated_subscription_status' => $subscriptionUpdated?->status?->value
        ]);

        if (!$subscriptionUpdated) {
            Log::error('Failed to update subscription', [
                'subscription_id' => $subscription->id,
                'plan_id' => $planId,
                'duration_id' => $durationId,
                'chat_id' => $chat->id
            ]);
            throw new RuntimeException('Не удалось обновить подписку');
        }

        // Начисляем лотерейные билеты за покупку подписки
        $this->awardLotteryTicketsForPurchase($subscriptionUpdated, $durationId);

        // Обрабатываем реферала (если есть)
        $this->processReferralBonus($subscriptionUpdated);
    }

    private function awardLotteryTicketsForPurchase(Subscription $subscription, int $durationId): void
    {
        try {
            $duration = \App\Models\Duration::find($durationId);
            if (!$duration) {
                Log::error('Duration not found for lottery tickets', ['duration_id' => $durationId]);
                return;
            }

            // Рассчитываем количество месяцев
            $days = $duration->days ?? 30;
            $months = max(1, round($days / 30));

            // Создаем билеты за покупку
            $lotteryService = app(\App\Services\Lottery\LotteryTicketService::class);
            $ticketsCreated = $lotteryService->createTicketsForSubscription($subscription, $months);

            Log::info('Lottery tickets awarded for purchase', [
                'subscription_id' => $subscription->id,
                'duration_days' => $days,
                'months' => $months,
                'tickets_created' => $ticketsCreated
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to award lottery tickets for purchase', [
                'subscription_id' => $subscription->id,
                'duration_id' => $durationId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function processReferralBonus(Subscription $subscription): void
    {
        try {
            // Проверяем, есть ли реферальный код
            if (!$subscription->referred_by_code_id) {
                return;
            }

            // Загружаем отношение referredByCode, если оно еще не загружено
            if (!$subscription->relationLoaded('referredByCode')) {
                $subscription->load('referredByCode.subscription');
            }

            $referralCode = $subscription->referredByCode;

            if (!$referralCode) {
                Log::warning('Referral code not found for subscription', [
                    'subscription_id' => $subscription->id,
                    'referred_by_code_id' => $subscription->referred_by_code_id
                ]);
                return;
            }

            $referrerSubscription = $referralCode->subscription;

            if (!$referrerSubscription) {
                Log::warning('Referrer subscription not found', [
                    'subscription_id' => $subscription->id,
                    'referral_code_id' => $referralCode->id
                ]);
                return;
            }

            // Проверяем, не начислялся ли уже билет за этого реферала
            $existingTicket = \App\Models\LotteryTicket::where('subscription_id', $referrerSubscription->id)
                ->where('source_type', \App\Enums\Lottery\LotteryTicketSourceEnum::REFERRAL_BONUS)
                ->where('source_id', $subscription->id) // ID приглашенного пользователя
                ->exists();

            if ($existingTicket) {
                Log::info('Referral bonus already awarded for this referred user', [
                    'referred_subscription_id' => $subscription->id,
                    'referrer_subscription_id' => $referrerSubscription->id,
                    'referral_code_id' => $referralCode->id
                ]);

                // Обнуляем referred_by_code_id, так как билет уже был начислен
                $subscription->referred_by_code_id = null;
                $subscription->save();

                return;
            }

            // Начисляем билет пригласившему пользователю
            $lotteryService = app(\App\Services\Lottery\LotteryTicketService::class);
            $ticketCreated = $lotteryService->createTicketForReferral(
                $referrerSubscription,
                $subscription->id // ID приглашенного пользователя
            );

            if ($ticketCreated) {
                // Увеличиваем количество лотерейных билетов на бонусном счете
                $bonusAccount = \App\Models\BonusAccount::getOrCreateForSubscription($referrerSubscription);
                $bonusAccount->addLotteryTickets(1);

                // Отправляем уведомление пригласившему пользователю
                if ($referrerSubscription->telegraphChat) {
                    $message = "🎉 Поздравляем!\n\n" .
                              "Ваш приглашенный друг купил подписку!\n" .
                              "Вам начислен 1 лотерейный билет. 🎫\n\n" .
                              "Участвуйте в розыгрыше и выигрывайте призы!";

                    $referrerSubscription->telegraphChat->message($message)->send();
                }

                Log::info('Referral bonus processed successfully', [
                    'referred_subscription_id' => $subscription->id,
                    'referrer_subscription_id' => $referrerSubscription->id,
                    'referral_code_id' => $referralCode->id
                ]);
            }

            // Обнуляем referred_by_code_id, чтобы не начислять билет повторно
            $subscription->referred_by_code_id = null;
            $subscription->save();

        } catch (\Exception $e) {
            Log::error('Failed to process referral bonus', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * @throws Exception
     */
    private function syncVpnKeys(Subscription $subscription, TelegraphChat $chat): void
    {
        Log::info('Starting VPN keys synchronization', [
            'subscription_id' => $subscription->id,
            'subscription_status' => $subscription->status->value,
            'chat_id' => $chat->id
        ]);

        $servers = app(SyncVpnKeyService::class, ['subscription' => $subscription])->handle();
        app(RemnawaveSyncVpnKeyService::class)->handle($subscription);

        if (!$servers) {
            Log::error("Не удалось обновить VPN ключи для чата: $chat->id", [
                'subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status->value,
                'chat_id' => $chat->id
            ]);
            throw new RuntimeException("Ошибка при обновлении VPN ключей");
        }

        Log::info("Remnawave VPN ключи обновлены для чата: $chat->id", [
            'subscription_id' => $subscription->id,
            'subscription_status' => $subscription->status->value,
            'chat_id' => $chat->id
        ]);
        Log::info("Marzban VPN ключи обновлены для чата: $chat->id", [
            'subscription_id' => $subscription->id,
            'subscription_status' => $subscription->status->value,
            'chat_id' => $chat->id
        ]);
    }

    private function storePaymentToDataBase(PaymentInterface $payment): void
    {
        $chatId = $payment->metadata['chat_id'] ?? null;
        $subscriptionId = $payment->metadata['subscription_id'] ?? null;

        if (!$subscriptionId && $chatId) {
            $subscriptionId = Subscription::query()->where('telegraph_chat_id', $chatId)->value('id');
        }

        if (!$subscriptionId) {
            throw new RuntimeException('Отсутствуют метаданные subscription_id/chat_id в платеже');
        }

        $paymentId = $payment->getId();
        $amount = $payment->amount->getValue();
        $paymentUrl = $payment->getConfirmation()?->getConfirmationUrl();

        $created = app(StorePaymentAction::class)->execute(new StorePaymentActionDto(
            subscriptionId: (int) $subscriptionId,
            yookassaPaymentId: $paymentId,
            amount: $amount,
            paymentUrl: $paymentUrl
        ));

        if (!$created) {
            throw new RuntimeException("Не удалось создать платеж для subscription_id: $subscriptionId");
        }
    }


    private function handleTicketNumberChangePayment(PaymentInterface $payment): void
    {
        try {
            if ($payment->status === 'succeeded' && $payment->paid) {
                $paymentDataBase = Payment::query()->where('yookassa_payment_id', $payment->getId())->first();

                if ($paymentDataBase && $paymentDataBase->status === PaymentStatusEnum::SUCCEEDED) {
                    Log::info("Платеж за смену номера билета уже был обработан ранее", [
                        'payment_id' => $payment->getId()
                    ]);
                    return;
                }

                if ($paymentDataBase) {
                    $paymentDataBase->status = PaymentStatusEnum::SUCCEEDED;
                    $paymentDataBase->save();
                }

                $numberChangeService = app(\App\Services\Lottery\LotteryTicketNumberChangeService::class);
                $paymentData = [
                    'id' => $payment->getId(),
                    'metadata' => $payment->metadata
                ];

                $success = $numberChangeService->processSuccessfulPayment($paymentData);

                if ($success) {
                    // Получаем чат через связь подписки
                    $subscription = $paymentDataBase->subscription ?? null;
                    if ($subscription && $subscription->telegraphChat) {
                        $oldNumber = $payment->metadata['old_number'] ?? 'неизвестен';
                        $newNumber = $payment->metadata['new_number'] ?? 'неизвестен';

                        $message = "✅ Платеж за смену номера билета успешно обработан!\n\n" .
                                  "Номер билета изменен с {$oldNumber} на {$newNumber}.\n\n" .
                                  "Перейдите в приложение и обновите страницу 'Мои билеты' для просмотра изменений.";

                        $subscription->telegraphChat->message($message)->send();
                    }

                    Log::info("Платеж за смену номера билета обработан успешно", [
                        'payment_id' => $payment->getId()
                    ]);
                } else {
                    Log::error("Не удалось обработать смену номера билета", [
                        'payment_id' => $payment->getId()
                    ]);
                }

            } elseif ($payment->status === 'canceled') {
                $paymentDataBase = Payment::query()->where('yookassa_payment_id', $payment->getId())->first();
                if ($paymentDataBase) {
                    $paymentDataBase->status = PaymentStatusEnum::CANCELED;
                    $paymentDataBase->save();
                }

                // Отправляем сообщение об отмене платежа
                $subscription = $paymentDataBase->subscription ?? null;
                if ($subscription && $subscription->telegraphChat) {
                    $message = "❌ Платеж за смену номера билета был отменен.\n\n" .
                              "Вы можете попробовать снова в приложении.";

                    $subscription->telegraphChat->message($message)->send();
                }

                Log::info("Платеж за смену номера билета отменен", [
                    'payment_id' => $payment->getId(),
                    'cancellation_details' => $payment->getCancellationDetails() ?? 'Не указано'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Ошибка обработки платежа за смену номера билета', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payment_id' => $payment->getId()
            ]);

            $paymentDataBase = Payment::query()->where('yookassa_payment_id', $payment->getId())->first();
            if ($paymentDataBase && $paymentDataBase->status !== PaymentStatusEnum::SUCCEEDED) {
                $paymentDataBase->status = PaymentStatusEnum::FAILED;
                $paymentDataBase->save();
            }

            throw $e;
        }
    }

    /**
     * Сохранить платеж за смену номера билета в базу данных
     */
    private function storeTicketNumberChangePaymentToDatabase(PaymentInterface $payment, array $paymentData): void
    {
        $subscriptionId = $paymentData['subscription_id'] ?? null;

        if (!$subscriptionId) {
            throw new RuntimeException('Отсутствует subscription_id в данных платежа');
        }

        $paymentId = $payment->getId();
        $amount = $payment->amount->getValue();
        $paymentUrl = $payment->getConfirmation()?->getConfirmationUrl();

        $paymentRecord = app(StorePaymentAction::class)->execute(new StorePaymentActionDto(
            subscriptionId: $subscriptionId,
            yookassaPaymentId: $paymentId,
            amount: $amount,
            paymentUrl: $paymentUrl
        ));

        if (!$paymentRecord) {
            Log::error("Не удалось создать платеж за смену номера билета", [
                'subscription_id' => $subscriptionId,
                'payment_id' => $paymentId
            ]);

            throw new RuntimeException("Не удалось создать платеж за смену номера билета");
        }
    }
}
