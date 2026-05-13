<?php

namespace App\Telegraph\Handlers\Connect\Steps\SetupApp;

use App\Enums\ClientApp\ClientAppDownloadUrlEnum;
use App\Models\ClientOperatingSystem;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class SetAppButtons
{
    public static function buttons(string $downloadUrl, int $osId): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('🔄 Установить приложение')->url($downloadUrl),
                Button::make('❌ Не удалось установить')->action('showInstallAppGuideAction')
                    ->param('os_id', $osId),
                Button::make('▶️ Продолжить')->action('setKeyAction')
                    ->param('os_id', $osId),
                Button::make('◀️ Назад')->action('selectOperatingSystemAction')
            ]);
    }

    public static function buttonsApple(ClientOperatingSystem $operatingSystem): Keyboard
    {
        $activeApp = $operatingSystem->activeClientApps()->first();
        $downloadUrls = $activeApp->getDownloadUrlsForOs($operatingSystem->id);

        $buttons = [];
        if ($downloadUrls
            ->where('download_url_type', ClientAppDownloadUrlEnum::RUS->value)
            ->value('download_url')) {
            $buttons[] = Button::make('🔄 Установить. AppStore Россия')
                ->url(
                    $downloadUrls
                        ->where('download_url_type', ClientAppDownloadUrlEnum::RUS->value)
                        ->value('download_url')
                );
        }

        if ($downloadUrls->where('download_url_type', ClientAppDownloadUrlEnum::GLOBAL->value)
            ->value('download_url')) {
            $buttons[] =  Button::make('🔄 Установить. AppStore Global')
                ->url(
                    $downloadUrls
                        ->where('download_url_type', ClientAppDownloadUrlEnum::GLOBAL->value)
                        ->value('download_url')
                );
        }


        return Keyboard::make()
            ->buttons(array_merge($buttons, [
                Button::make('❌ Не удалось установить')->action('showInstallAppGuideAction')
                    ->param('os_id', $operatingSystem->id),
                Button::make('▶️ Продолжить')->action('setKeyAction')
                    ->param('os_id', $operatingSystem->id),
                Button::make('◀️ Назад')->action('selectOperatingSystemAction')
            ]));
    }
}
