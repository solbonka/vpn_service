<?php

use App\Enums\Payment\PaymentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('yookassa_payment_id', 36)->unique();
            $table->enum('status', [
                PaymentStatusEnum::PENDING->value,
                PaymentStatusEnum::SUCCEEDED->value,
                PaymentStatusEnum::CANCELED->value,
                PaymentStatusEnum::FAILED->value
            ])->default(PaymentStatusEnum::PENDING->value);
            $table->decimal('amount');
            $table->string('currency')->default('RUB');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
