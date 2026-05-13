<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->string('type', 55)->nullable()->after('id');
            $table->text('value')->nullable()->after('type');
        });

        $existingSettings = \DB::table('miniapp_settings')->first();
        if ($existingSettings) {
            if ($existingSettings->logo) {
                \DB::table('miniapp_settings')->insert([
                    'type' => 'logo',
                    'value' => $existingSettings->logo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($existingSettings->lottery_prize_image) {
                \DB::table('miniapp_settings')->insert([
                    'type' => 'lottery_prize',
                    'value' => $existingSettings->lottery_prize_image,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->dropColumn(['logo', 'lottery_prize_image']);
        });

        \DB::table('miniapp_settings')->where('id', 1)->whereNull('type')->delete();

        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->string('type', 55)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->dropColumn(['type', 'value']);
        });

        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('id');
            $table->string('lottery_prize_image', 55)->nullable()->after('logo');
        });
    }
};
