<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lottery_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_number', 4)->unique();
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'source_type']);
            $table->index('ticket_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lottery_tickets');
    }
};
