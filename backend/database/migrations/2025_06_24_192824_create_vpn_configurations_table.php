<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vpn_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('private_key');
            $table->string('public_key');
            $table->json('short_ids');
            $table->integer('port');
            $table->text('base_vless_link');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vpn_configurations');
    }
};
