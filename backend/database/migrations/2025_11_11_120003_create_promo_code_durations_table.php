<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_code_durations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('duration_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_code_id', 'duration_id']);
            $table->index('promo_code_id');
            $table->index('duration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_durations');
    }
};

