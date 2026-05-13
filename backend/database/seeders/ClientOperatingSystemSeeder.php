<?php

namespace Database\Seeders;

use App\Models\ClientOperatingSystem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientOperatingSystemSeeder extends Seeder
{
    public function run(): void
    {
        $systems = [
            '🍏 iPhone || iPad' => 'ios',
            '🤖 Android' => 'android',
            '🌐 Huawei || Honor' => 'huawei',
            '💻 Mac' => 'mac',
            '🪟 Windows' => 'windows',
            '📺 Android TV' => 'android_tv'
        ];

        foreach ($systems as $system => $slug) {
            ClientOperatingSystem::firstOrCreate([
                'name' => $system,
                'slug' => $slug
            ]);
        }
    }
}
