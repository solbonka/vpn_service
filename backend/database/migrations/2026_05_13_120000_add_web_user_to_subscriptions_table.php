<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('web_user_id')->nullable()->after('telegraph_chat_id')->constrained('web_users')->nullOnDelete();
            $table->foreignId('telegraph_chat_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('web_user_id');
            $table->foreignId('telegraph_chat_id')->nullable(false)->change();
        });
    }
};
