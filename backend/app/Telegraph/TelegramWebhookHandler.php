<?php

namespace App\Telegraph;

use App\Enums\Payment\PaymentStatusEnum;
use App\Enums\Subscription\SubscriptionStatusEnum;
use App\Enums\Telegram\TelegramMessageEnum;
use App\Helpers\DeleteMessageHelper;
use App\Helpers\MenuKeyboardHelper;
use App\Models\Message;
use App\Models\Payment;
use App\Models\ReferralCode;
use App\Models\Subscription;
use App\Services\Referral\ReferralProcessingService;
use App\Services\Subscription\SubscriptionService;
use App\Telegraph\Handlers\Author\AuthorButtonHandler;
use App\Telegraph\Handlers\Connect\ConnectButtonHandler;
use App\Telegraph\Handlers\Connect\Steps\ChannelSubscription\ChannelSubscriptionHandler;
use App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\Happ\HappGuideHandler;
use App\Telegraph\Handlers\Connect\Steps\Guides\ConnectKeyToAppGuide\V2RayTun\V2RayTunGuideHandler;
use App\Telegraph\Handlers\Connect\Steps\Guides\InstallAppGuide\AppGuideHandler;
use App\Telegraph\Handlers\Connect\Steps\OperatingSystem\OperatingSystemHandler;
use App\Telegraph\Handlers\Connect\Steps\SetupApp\SetupAppHandler;
use App\Telegraph\Handlers\Connect\Steps\VpnKeyDelivery\VpnKeyDeliveryHandler;
use App\Telegraph\Handlers\Extension\ExtensionButtonHandler;
use App\Telegraph\Handlers\Extension\Steps\Duration\DurationHandler;
use App\Telegraph\Handlers\Extension\Steps\Payment\CreatePayment\CreatePaymentHandler;
use App\Telegraph\Handlers\Extension\Steps\Payment\SelectPayment\SelectPaymentHandler;
use App\Telegraph\Handlers\Extension\Steps\Tariff\TariffHandler;
use App\Telegraph\Handlers\Help\HelpButtonHandler;
use App\Telegraph\Handlers\MiniApp\SendMiniAppHandler;
use App\Telegraph\Handlers\Payment\BackToPayment\BackToPaymentHandler;
use App\Telegraph\Handlers\Payment\RequestSharePayment\RequestSharePaymentHandler;
use App\Telegraph\Handlers\PromoCode\CreatePaymentWithPromoCodeHandler;
use App\Telegraph\Handlers\PromoCode\EnterPromoCodeHandler;
use App\Telegraph\Handlers\PromoCode\PromoCodeMessageHandler;
use App\Telegraph\Handlers\Status\StatusButtonHandler;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

class TelegramWebhookHandler extends WebhookHandler
{
    use ErrorMessageTrait;

    private array $messageHandlers = [
        PromoCodeMessageHandler::class,
        ConnectButtonHandler::class,
        ExtensionButtonHandler::class,
        AuthorButtonHandler::class,
        HelpButtonHandler::class,
        StatusButtonHandler::class,
        SendMiniAppHandler::class,
    ];

    public function start(): void
    {
        $this->updateChatUserInfo();

        $referralCode = $this->extractReferralCodeFromMessage();

        if ($referralCode) {
            $this->processReferralAndGetMenu($referralCode);
        } else {
            $this->getMenu();
        }
    }

    private function updateChatUserInfo(): void
    {
        try {
            $from = $this->message?->from();

            if ($from) {
                $this->chat->first_name = $from->firstName();
                $this->chat->last_name = $from->lastName();
                $this->chat->username = $from->username();
                $this->chat->save();
            }
        } catch (\Exception $e) {
            Log::error('Failed to update chat user info from webhook', [
                'chat_id' => $this->chat->chat_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function getMenu(): void
    {
        $subscription = $this->checkSubscription();

        if (! $subscription) {
            return;
        }

        if ($subscription->status === SubscriptionStatusEnum::BLOCKED) {
            $buttonText = '🔄️ Продлить подписку';
        } else {
            $buttonText = '⚡️ Подключиться!';
        }

        $message = Message::query()->where('telegraph_bot_id', $this->bot->id)
            ->where('key', TelegramMessageEnum::START)->first()?->text
            ?: "

VPN - подключение в 3️⃣ шага🔥

🚀️ Высокая скорость, безлимитный трафик

👫 Оперативная и дружелюбная поддержка

Нажмите $buttonText и начните использовать сервис уже сегодня.
";

        MenuKeyboardHelper::send($this->chat, $message);
    }

    protected function handleChatMessage(Stringable $text): void
    {
        $message = $text->toString();

        foreach ($this->messageHandlers as $messageHandlerClass) {
            $messageHandler = new $messageHandlerClass($this->bot, $this->chat);
            if ($messageHandler->canHandle($message)) {
                $messageHandler->handle($message);
            }
        }

        $currentMessageId = $this->message->id();

        $this->deleteMessage($currentMessageId);
    }

    private function deleteMessage(?int $messageId = null): void
    {
        if ($messageId) {
            try {
                $this->chat->deleteMessage($messageId)->send();
            } catch (Exception $e) {
                Log::error('Error deleting message:', ['error' => $e->getMessage()]);
            }
        }
    }

    public function getMenuAction(): void
    {
        $this->getMenu();
    }

    /**
     * @throws Exception
     */
    public function checkChannelSubscriptionAction(): void
    {
        app(ChannelSubscriptionHandler::class, [
            'bot' => $this->bot,
            'chat' => $this->chat
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function selectOperatingSystemAction(): void
    {
        app(OperatingSystemHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function setupAppAction(): void
    {
        app(SetupAppHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function showInstallAppGuideAction(): void
    {
        app(AppGuideHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function setKeyAction(): void
    {
        app(VpnKeyDeliveryHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function showConnectToV2RayTunGuideAction(): void
    {
        app(V2RayTunGuideHandler::class, [
            'chat' => $this->chat,
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function showConnectToHappGuideAction(): void
    {
        app(HappGuideHandler::class, [
            'chat' => $this->chat,
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function selectTariffAction(): void
    {
        app(TariffHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function selectDurationAction(): void
    {
        app(DurationHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function selectPaymentMethodAction(): void
    {
        app(SelectPaymentHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function createPaymentAction(): void
    {
        app(CreatePaymentHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function requestSharePaymentAction(): void
    {
        app(RequestSharePaymentHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function enterPromoCodeAction(): void
    {
        app(EnterPromoCodeHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function createPaymentWithPromoCodeAction(): void
    {
        app(CreatePaymentWithPromoCodeHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function payWithPromoAction(): void
    {
        app(CreatePaymentWithPromoCodeHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function backToPaymentAction(): void
    {
        app(BackToPaymentHandler::class, [
            'chat' => $this->chat,
            'callbackQuery' => $this->callbackQuery
        ])->handle();
    }

    /**
     * @throws Exception
     */
    public function updateMenuAction(): void
    {
        $chatId = $this->callbackQuery->data()['chat_id'] ?? null;

        if (! $chatId) {
            throw new Exception('chat_id not found');
        }

        $telegraphChat = TelegraphChat::query()->findOrFail($chatId);

        DeleteMessageHelper::delete($telegraphChat, $this->callbackQuery);

        $message = '
Вы успешно обновили меню!
        ';

        MenuKeyboardHelper::send($telegraphChat, $message);
    }

    /**
     * @throws Exception
     */
    public function storeSubscriptionAction(): void
    {
        $callbackQuery = $this->callbackQuery;
        $chatId = $callbackQuery->data()['chat_id'] ?? null;

        if (! $chatId) {
            throw new Exception('chat_id not found');
        }

        $telegraphChat = TelegraphChat::query()->findOrFail($chatId);

        if (! $this->checkSubscription()) {
            return;
        }

        DeleteMessageHelper::delete($telegraphChat, $callbackQuery);

        $message = "
Отлично, подписка успешно получена, вы можете пользоваться VPN.

Для этого нажмите '⚡️ Подключиться!'
        ";

        $telegraphChat->message($message)->send();
    }

    private function checkSubscription(): Subscription|null
    {
        $subscription = Subscription::query()->where('telegraph_chat_id', $this->chat->id)->first();

        if (! $subscription) {
            $subscription = app(SubscriptionService::class, ['chat' => $this->chat])->store();

            if (! $subscription) {
                Log::error("Не удалось создать подписку для чата: {$this->chat->id}");

                $this->chat->message($this->getErrorMessage())
                    ->send();

                return null;
            }
        }

        return $subscription;
    }

    private function extractReferralCodeFromMessage(): ?string
    {
        $messageText = $this->message?->text() ?? '';

        if (preg_match('/^\/start\s+ref_([A-Z0-9]{8})$/', $messageText, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function processReferralAndGetMenu(string $referralCode): void
    {
        $subscription = $this->checkSubscription();

        if (!$subscription) {
            return;
        }

        // Проверяем, что у подписки еще не установлен реферальный код
        if ($subscription->referred_by_code_id) {
            Log::info('Subscription already has a referral code', [
                'subscription_id' => $subscription->id,
                'existing_referral_code_id' => $subscription->referred_by_code_id,
                'new_referral_code' => $referralCode
            ]);
            $this->getMenu();
            return;
        }

        // Находим реферальный код
        $referralCodeModel = ReferralCode::where('code', $referralCode)
            ->where('is_active', true)
            ->with('subscription')
            ->first();

        if (!$referralCodeModel) {
            Log::warning('Referral code not found or inactive', [
                'code' => $referralCode,
                'subscription_id' => $subscription->id
            ]);
            $this->sendReferralFailedMessage();
            $this->getMenu();
            return;
        }

        // Проверяем, что пользователь не приглашает сам себя
        if ($referralCodeModel->subscription->telegraph_chat_id === $subscription->telegraph_chat_id) {
            Log::warning('User tried to refer themselves', [
                'subscription_id' => $subscription->id,
                'referral_code' => $referralCode
            ]);
            $this->sendReferralFailedMessage();
            $this->getMenu();
            return;
        }

        // КРИТИЧЕСКИ ВАЖНО: Проверяем, что это НОВЫЙ пользователь (нет успешных платежей)
        $successfulPaymentsCount = \App\Models\Payment::where('subscription_id', $subscription->id)
            ->where('status', \App\Enums\Payment\PaymentStatusEnum::SUCCEEDED)
            ->count();

        if ($successfulPaymentsCount > 0) {
            Log::info('User already has successful payments, cannot use referral code', [
                'subscription_id' => $subscription->id,
                'successful_payments_count' => $successfulPaymentsCount,
                'referral_code' => $referralCode
            ]);
            $this->sendReferralFailedForExistingUserMessage();
            $this->getMenu();
            return;
        }

        // Сохраняем реферальный код в подписке
        $subscription->referred_by_code_id = $referralCodeModel->id;
        $subscription->save();

        Log::info('Referral code saved to subscription', [
            'subscription_id' => $subscription->id,
            'referral_code_id' => $referralCodeModel->id,
            'referral_code' => $referralCode
        ]);

        $this->sendReferralSuccessMessage();
        $this->getMenu();
    }

    private function sendReferralSuccessMessage(): void
    {
        $message = "🎉 Отлично! Вы перешли по реферальной ссылке.\n\n" .
                  "Ваш друг получит лотерейный билет, когда вы купите любую подписку!\n\n" .
                  "Добро пожаловать в наш VPN сервис! 🚀";

        $this->chat->message($message)->send();
    }

    private function sendReferralFailedMessage(): void
    {
        $message = "👋 Добро пожаловать в наш VPN сервис!\n\n" .
                  "К сожалению, реферальный код недействителен или истек.\n\n" .
                  "Но это не проблема - вы все равно можете пользоваться нашим сервисом! 🚀";

        $this->chat->message($message)->send();
    }

    private function sendReferralFailedForExistingUserMessage(): void
    {
        $message = "👋 Добро пожаловать обратно!\n\n" .
                  "Реферальные ссылки предназначены только для новых пользователей.\n" .
                  "Вы уже являетесь нашим клиентом! 🎉\n\n" .
                  "Продолжайте пользоваться сервисом! 🚀";

        $this->chat->message($message)->send();
    }
}
