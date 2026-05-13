<?php

namespace App\Telegraph\Handlers\Extension\Steps\Tariff;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\PlanDescriptionHelper;
use App\Models\Plan;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Log;

class TariffHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private ?CallbackQuery $callbackQuery;

    public function __construct(TelegraphChat $chat, ?CallbackQuery $callbackQuery = null)
    {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        $isExtension = false;

        if ($this->callbackQuery) {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $data = $this->callbackQuery->data();
            $isExtension = filter_var($data['is_extension'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );
        }

        $plans = Plan::paidWithActiveServers()->with('servers')->get();

        if ($plans->isEmpty()) {
            Log::info('Отсутствуют планы для активных регионов или нет активных регионов');

            $this->chat->message($this->getErrorMessage())
                ->send();

            return;
        }

        $buttons = [];
        $planDescriptions = [];

        foreach ($plans as $plan) {
            $buttons[] = TariffButtons::buttonPlan($plan, $isExtension);

            $planDescriptions[] = PlanDescriptionHelper::getDescription($plan);
        }

        $this->chat->message(TariffMessage::message($planDescriptions))
            ->markdown()
            ->keyboard(TariffButtons::buttons($buttons))
            ->send();
    }
}
