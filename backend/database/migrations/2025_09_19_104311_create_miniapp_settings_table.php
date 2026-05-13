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
        Schema::create('miniapp_settings', function (Blueprint $table) {
            $table->id();
            $table->longText('logo')->nullable();
            $table->timestamps();
        });

        // Создаем единственную запись с пустым логотипом
        DB::table('miniapp_settings')->insert([
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('miniapp_settings');
    }
};
