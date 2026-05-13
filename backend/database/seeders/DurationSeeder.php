<?php

namespace Database\Seeders;

use App\Models\Duration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DurationSeeder extends Seeder
{
    public function run(): void
    {
        $durations = [
            [
                'name' => '1 неделя',
                'days' => 7,
                'is_trial' => true
            ],
            [
                'name' => '1 месяц',
                'days' => 30
            ],
            [
                'name' => '2 месяца',
                'days' => 60,
                'discount_percentage' => 5
            ],
            [
                'name' => '3 месяца',
                'days' => 90,
                'discount_percentage' => 10
            ]
        ];

        foreach ($durations as $duration) {
            Duration::firstOrCreate(
                ['days' => $duration['days']],
                $duration
            );
        }
    }
}
