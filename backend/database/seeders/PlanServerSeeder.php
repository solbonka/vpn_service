<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Region;
use App\Models\Server;
use Illuminate\Database\Seeder;

class PlanServerSeeder extends Seeder
{
    public function run(): void
    {
        $basicPlan = Plan::where('name', 'Базовый')->first();
        $trialPlan = Plan::where('name', 'Пробный')->first();

        $activeMainServerIds = Server::where('is_active', true)->pluck('id');

        if ($basicPlan) {
            $basicPlan->servers()->syncWithoutDetaching($activeMainServerIds);
        }

        if ($trialPlan) {
            $trialPlan->servers()->syncWithoutDetaching($activeMainServerIds);
        }
    }
}
