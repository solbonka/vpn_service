<?php

namespace App\Telegraph\Handlers\Connect\Steps\SetupApp;

use App\Helpers\DeleteMessageHelper;
use App\Helpers\OperatingSystemHelper;
use App\Models\ClientOperatingSystem;
use App\Traits\Error\ErrorMessageTrait;
use DefStudio\Telegraph\DTO\CallbackQuery;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;
use Exception;
use Illuminate\Support\Facades\Log;

class SetupAppHandler
{
    use ErrorMessageTrait;

    private TelegraphChat $chat;
    private CallbackQuery $callbackQuery;

    public function __construct(TelegraphChat $chat, CallbackQuery $callbackQuery) {
        $this->chat = $chat;
        $this->callbackQuery = $callbackQuery;
    }

    public function handle(): void
    {
        try {
            DeleteMessageHelper::delete($this->chat, $this->callbackQuery);

            $osId = $this->callbackQuery->data()['os_id'] ?? null;

            Log::info('id ОС ' . $osId);

            if (!$osId) {
                throw new Exception('Operating system ID not found');
            }

            $this->chat->client_operating_system_id = $osId;
            if ($this->chat->isDirty('client_operating_system_id')) {
                $this->chat->save();
            }

            $operatingSystem = ClientOperatingSystem::find($osId);
            $nameOs = OperatingSystemHelper::mapOperatingSystem($operatingSystem->name);

            $isApple = $nameOs == 'ios' || $nameOs == 'mac';

            Log::info('название операционной системы ' . $nameOs);

            $downloadUrl = OperatingSystemHelper::getDownloadUrl($operatingSystem->name);

            $this->chat->message($isApple ? SetupAppMessage::messageApple() : SetupAppMessage::message())
                ->markdown()
                ->keyboard($isApple ? SetAppButtons::buttonsApple($operatingSystem) : SetAppButtons::buttons($downloadUrl, $osId))
                ->send();

        } catch (Exception $e) {
            Log::error('Error in setupApp:', ['error' => $e->getMessage()]);

            Telegraph::chat($this->chat)->message($this->getErrorMessage())->send();
        }
    }
}
