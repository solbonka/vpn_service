<?php

namespace App\Jobs\SupportMessage;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSupportMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;
    private TelegraphChat $chat;
    private string $supportChanelName;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->supportChanelName = config('telegram.support_chanel_name');
    }

    public function handle(): void
    {
        $message = "Если у Вас возникли вопросы по подключению - обратитесь в поддержку $this->supportChanelName";

        $this->chat->message($message)->send();
    }
}
