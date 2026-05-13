<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('remnawave_vpn_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('uuid');
            $table->string('username');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Один ключ на подписку
            $table->unique('subscription_id');
            
            // Индексы для быстрого поиска
            $table->index(['subscription_id', 'is_active']);
            $table->index('uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remnawave_vpn_keys');
    }
};
