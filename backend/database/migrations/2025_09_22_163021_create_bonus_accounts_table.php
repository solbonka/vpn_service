<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->integer('balance_rubles')->default(0);
            $table->integer('balance_days')->default(0);
            $table->integer('balance_lottery_tickets')->default(0);
            $table->timestamps();

            $table->unique('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_accounts');
    }
};
