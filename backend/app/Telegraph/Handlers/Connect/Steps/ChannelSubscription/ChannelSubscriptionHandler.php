<?php

namespace App\Telegraph\Handlers\Connect\Steps\ChannelSubscription;

use App\Telegraph\Handlers\Connect\Steps\OperatingSystem\OperatingSystemHandler;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChannelSubscriptionHandler
{
    private TelegraphBot $bot;
    private TelegraphChat $chat;
    private string $chanelName;
    private string $chanelLink;
    private bool $isCheckSubscriptionToChanel;

    public function __construct(TelegraphBot $bot, TelegraphChat $chat) {
        $this->bot = $bot;
        $this->chat = $chat;
        $this->chanelName = "@VpnBichurskoeBot";
        //$this->chanelName = config('telegram.chanel_name');
        $this->chanelLink = 'https://t.me/vpn_bichurskoe';
        //$this->chanelLink = config('telegram.chanel_link');
        $this->isCheckSubscriptionToChanel = config('telegram.check_subscription_to_chanel');
    }

    /**
     * @throws Exception
     */
    public function handle(): void
    {
        if (!$this->isCheckSubscriptionToChanel) {

            app(OperatingSystemHandler::class, [
                'chat' => $this->chat,
            ])->handle();

            return;
        }

        try {
            $response = Http::get('https://api.telegram.org/bot' . $this->bot->token . '/getChatMember', [
                'chat_id' => $this->chanelName,
                'user_id' => $this->chat->chat_id
            ]);

            if (!$response->ok() || in_array($response->json('result.status'), ['left', 'kicked'])) {
                $this->chat->message(ChannelSubscriptionMessage::message($this->chanelName))
                    ->markdown()
                    ->keyboard(ChannelSubscriptionButtons::buttons($this->chanelLink))
                    ->send();
            } else {
                app(OperatingSystemHandler::class, [
                    'chat' => $this->chat,
                ])->handle();
            }
        } catch (\Exception $e) {
            Log::error('Subscription check error:', ['error' => $e->getMessage()]);
        }
    }
}
