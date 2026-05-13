<?php

namespace App\Jobs\ConnectionMessage;

use App\Models\CustomTelegraphChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendConnectionMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 3;
    private CustomTelegraphChat $chat;

    public function __construct(CustomTelegraphChat $chat)
    {
        $this->chat = $chat;
    }

    public function handle(): void
    {
        $connectImgPath = Storage::disk('public')->path('images/connect/connect.jpeg');

        $message = "
Отлично! VPN успешно добавлен и готов к использованию!🥳

✅ Нажмите на кнопку включения VPN (см. изображение выше).

🎉 Наслаждайтесь подключением! 🎉
";

        $this->chat->message($message)
            ->photo($connectImgPath)
            ->markdown()
            ->send();
    }
}
