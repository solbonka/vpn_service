<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->string('lottery_prize_image', 55)->nullable()->after('logo');
        });
    }

    public function down(): void
    {
        Schema::table('miniapp_settings', function (Blueprint $table) {
            $table->dropColumn('lottery_prize_image');
        });
    }
};
