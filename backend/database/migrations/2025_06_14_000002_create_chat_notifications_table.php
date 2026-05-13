<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telegraph_chat_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('notification_type')->nullable(false);
            $table->timestamps();

            $table->unique(['telegraph_chat_id', 'notification_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_notifications');
    }
};

