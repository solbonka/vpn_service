<?php

use App\Enums\Subscription\SubscriptionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('token', 32)->nullable()->unique();
            $table->foreignId('telegraph_chat_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('plan_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreignId('duration_id')->nullable()
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->enum('status', [
                SubscriptionStatusEnum::ACTIVE->value,
                SubscriptionStatusEnum::BLOCKED->value
            ])->default(SubscriptionStatusEnum::ACTIVE->value);
            $table->timestamp('end_datetime');
            $table->timestamps();

            $table->unique(['telegraph_chat_id', 'plan_id'], 'unique_chat_plan_soft');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
