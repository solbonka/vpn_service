<?php

namespace Database\Seeders;

use App\Models\ClientApp;
use Illuminate\Database\Seeder;

class ClientAppSeeder extends Seeder
{
    public function run(): void
    {
        $apps = [
            ['name' => 'v2RayTun', 'description' => ''],
            ['name' => 'Hiddify', 'description' => ''],
            ['name' => 'Happ', 'description' => ''],
        ];

        foreach ($apps as $app) {
            ClientApp::firstOrCreate(
                ['name' => $app['name']],
                ['description' => $app['description']]
            );
        }
    }
}
