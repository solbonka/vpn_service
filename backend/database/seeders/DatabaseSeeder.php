<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ClientAppSeeder::class,
            ClientOperatingSystemSeeder::class,
            ClientAppOperatingSystemSeeder::class,
            PlanSeeder::class,
            ServerSeeder::class,
            DurationSeeder::class,
            PlanServerSeeder::class,
            VpnConfigurationSeeder::class,
            AuthorInfoSeeder::class
        ]);
    }
}
