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
        Schema::create('client_app_download_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_app_operating_system_id')
                ->constrained('client_app_operating_system')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('download_url_type');
            $table->string('download_url');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_app_operating_system_id', 'download_url_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_app_download_urls');
    }
};
