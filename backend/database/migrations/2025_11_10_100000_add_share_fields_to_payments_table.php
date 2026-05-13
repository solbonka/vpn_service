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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('share_token', 32)
                ->nullable()
                ->unique()
                ->after('amount')
                ->comment('Токен для публичной share-ссылки');
            
            $table->text('payment_url')
                ->nullable()
                ->after('share_token')
                ->comment('URL оплаты от YooKassa');
            
            $table->integer('share_views_count')
                ->default(0)
                ->after('payment_url')
                ->comment('Количество просмотров share-ссылки');
            
            $table->index('share_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['share_token']);
            $table->dropColumn(['share_token', 'payment_url', 'share_views_count']);
        });
    }
};


