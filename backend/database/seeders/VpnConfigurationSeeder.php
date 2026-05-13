<?php

namespace Database\Seeders;

use App\Models\VpnConfiguration;
use Illuminate\Database\Seeder;

class VpnConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $vpnConfigurations = [
            [
                'private_key' => '0Oxg5B_GUvE_GfE4fldCAS0vwyDWp9ORBbZnH8Z9QCI',
                'public_key' => 'LEUuplvXcqqtM3Ni76v7_rTQzzmZWVk3hF0SjdPAKz8',
                'short_ids' => [
                    "dd3b257eabe14d18"
                ],
                'port' => 2040,
                'base_vless_link' => 'type=tcp&security=reality&fp=chrome&sni=tradingview.com&spx=%2F'
            ]
        ];

        foreach ($vpnConfigurations as $vpnConfiguration) {
            VpnConfiguration::firstOrCreate(
                ['port' => $vpnConfiguration['port']],
                $vpnConfiguration
            );
        }
    }
}
