<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bonus_accounts', function (Blueprint $table) {
            $table->dropColumn('balance_lottery_tickets');
        });
    }

    public function down(): void
    {
        Schema::table('bonus_accounts', function (Blueprint $table) {
            $table->integer('balance_lottery_tickets')->default(0)->after('balance_days');
        });
    }
};
