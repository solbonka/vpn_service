<?php

namespace App\Helpers;

use App\Models\ClientOperatingSystem;

class OperatingSystemHelper
{
    private const OS_CONFIG = [
        '🍏 iPhone || iPad' => [
            'slug' => 'ios',
        ],
        '🤖 Android' => [
            'slug' => 'android',
        ],
        '💻 Mac' => [
            'slug' => 'mac',
        ],
        '🪟 Windows' => [
            'slug' => 'windows',
        ],
        '🌐 Huawei || Honor' => [
            'slug' => 'huawei',
        ],
        '📺 Android TV' => [
            'slug' => 'android_tv',
        ],
    ];

    public static function getDownloadUrl(?string $osName): ?string
    {
        if (!$osName || !isset(self::OS_CONFIG[$osName])) {
            return null;
        }

        $os = ClientOperatingSystem::query()->where('name', $osName)->first();
        if (!$os) {
            return null;
        }

        $activeApp = $os->activeClientApps()->first();
        return $activeApp?->pivot?->download_url;
    }

    public static function mapOperatingSystem(?string $input): string
    {
        if (!$input || !isset(self::OS_CONFIG[$input])) {
            return 'unknown';
        }

        return self::OS_CONFIG[$input]['slug'];
    }
}
