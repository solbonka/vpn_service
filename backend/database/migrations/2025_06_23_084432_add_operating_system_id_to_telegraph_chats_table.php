<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->foreignId('client_operating_system_id')->nullable()
                ->after('telegraph_bot_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('telegraph_chats', function (Blueprint $table) {
            $table->dropForeign(['operating_system_id']);
            $table->dropColumn('operating_system_id');
        });
    }
};
