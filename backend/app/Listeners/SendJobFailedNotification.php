<?php

namespace App\Listeners;

use App\Notifications\QueueAlertNotification;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\Events\JobFailed;

class SendJobFailedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     */
    public function handle(JobFailed $event): void
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        try {
            (new Client())->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'form_params' => [
                    'chat_id' => $chatId,
                    'text' => "❌ Джоба упала\nQueue: {$event->job->getQueue()}\nJob: {$event->job->resolveName()}\nError: {$event->exception->getMessage()}",
                    'parse_mode' => 'HTML', // можно 'MarkdownV2'
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Ошибка отправки уведомления в Telegram: " . $e->getMessage());
        }
    }
}
