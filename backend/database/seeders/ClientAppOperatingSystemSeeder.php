<?php

namespace Database\Seeders;

use App\Models\ClientApp;
use App\Models\ClientOperatingSystem;
use App\Models\ClientAppOperatingSystem;
use App\Models\ClientAppDownloadUrl;
use App\Enums\ClientApp\ClientAppDownloadUrlEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientAppOperatingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $v2RayTun = ClientApp::where('name', 'v2RayTun')->first();
        $hiddify = ClientApp::where('name', 'Hiddify')->first();
        $happ = ClientApp::where('name', 'Happ')->first();

        $iPhone = ClientOperatingSystem::where('name', '🍏 iPhone || iPad')->first();
        $android = ClientOperatingSystem::where('name', '🤖 Android')->first();
        $huawei = ClientOperatingSystem::where('name', '🌐 Huawei || Honor')->first();
        $mac = ClientOperatingSystem::where('name', '💻 Mac')->first();
        $windows = ClientOperatingSystem::where('name', '🪟 Windows')->first();
        $androidTv = ClientOperatingSystem::where('name', '📺 Android TV')->first();

        // v2RayTun
        $this->createClientAppOperatingSystem($v2RayTun, [
            $iPhone->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apps.apple.com/ru/app/v2raytun/id647662895'],
                    ['type' => ClientAppDownloadUrlEnum::GLOBAL, 'url' => 'https://apps.apple.com/us/app/v2raytun/id6476628951']
                ]
            ],
            $android->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://play.google.com/store/apps/details?id=com.v2raytun.android']
                ]
            ],
            $huawei->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apkcombo.app/ru/v2raytun/com.v2raytun.android']
                ]
            ],
        ]);

        // Hiddify
        $this->createClientAppOperatingSystem($hiddify, [
            $mac->id => [
                'is_active' => false,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apps.apple.com/ru/app/hiddify-proxy-vpn/id6596777532']
                ]
            ],
            $windows->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apps.microsoft.com/detail/9pdfnl3qv2s5?hl=en-us&gl=RU']
                ]
            ],
        ]);

        // Happ
        $this->createClientAppOperatingSystem($happ, [
            $iPhone->id => [
                'is_active' => false,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apps.apple.com/ru/app/happ-proxy-utility-plus/id6746188973'],
                    ['type' => ClientAppDownloadUrlEnum::GLOBAL, 'url' => 'https://apps.apple.com/us/app/happ-proxy-utility/id6504287215']
                ]
            ],
            $android->id => [
                'is_active' => false,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://play.google.com/store/apps/details?id=com.happproxy']
                ]
            ],
            $mac->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://apps.apple.com/ru/app/happ-proxy-utility-plus/id6746188973'],
                    ['type' => ClientAppDownloadUrlEnum::GLOBAL, 'url' => 'https://apps.apple.com/us/app/happ-proxy-utility/id6504287215?platform=mac']
                ]
            ],
            $androidTv->id => [
                'is_active' => true,
                'download_urls' => [
                    ['type' => ClientAppDownloadUrlEnum::RUS, 'url' => 'https://play.google.com/store/apps/details?id=com.happproxy']
                ]
            ],
        ]);
    }

    private function createClientAppOperatingSystem(ClientApp $clientApp, array $operatingSystems): void
    {
        foreach ($operatingSystems as $osId => $data) {
            $clientAppOperatingSystem = ClientAppOperatingSystem::updateOrCreate([
                'client_app_id' => $clientApp->id,
                'client_operating_system_id' => $osId,
            ], [
                'is_active' => $data['is_active'],
            ]);

            if (!$clientAppOperatingSystem->wasRecentlyCreated) {
                $clientAppOperatingSystem->update([
                    'is_active' => $data['is_active'],
                ]);
            }

            if (!empty($data['download_urls'])) {
                foreach ($data['download_urls'] as $urlData) {
                    ClientAppDownloadUrl::updateOrCreate([
                        'client_app_operating_system_id' => $clientAppOperatingSystem->id,
                        'download_url_type' => $urlData['type']->value,
                    ], [
                        'download_url' => $urlData['url'],
                        'is_active' => true
                    ]);
                }
            }
        }
    }
}
