<?php

namespace App\Models;

use App\Enums\Payment\PaymentStatusEnum;
use App\Models\Relations\HasPaymentRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperPayment
 */
class Payment extends Model
{
    use HasPaymentRelations;

    protected $fillable = [
        'subscription_id',
        'promo_code_id',
        'yookassa_payment_id',
        'status',
        'amount',
        'currency',
        'share_token',
        'payment_url',
        'share_views_count'
    ];

    protected $casts = [
        'status' => PaymentStatusEnum::class
    ];

    /**
     * Генерировать уникальный share-токен
     */
    public static function generateUniqueShareToken(): string
    {
        do {
            $token = Str::random(16);
        } while (self::where('share_token', $token)->exists());

        return $token;
    }

    /**
     * Создать share-токен для этого платежа
     */
    public function createShareToken(): string
    {
        if ($this->share_token) {
            return $this->share_token; // Уже существует
        }

        $token = self::generateUniqueShareToken();
        $this->update(['share_token' => $token]);

        return $token;
    }

    /**
     * Получить полный URL для share-ссылки
     */
    public function getShareUrl(): ?string
    {
        if (!$this->share_token) {
            return null;
        }

        $domain = config('telegram.mini_app_domain');
        
        if (!$domain) {
            return null;
        }

        return $domain . '/#/pay/' . $this->share_token;
    }

    /**
     * Проверить, можно ли оплатить этот платеж
     */
    public function isPayable(): bool
    {
        return $this->status === PaymentStatusEnum::PENDING;
    }

    /**
     * Увеличить счетчик просмотров share-ссылки
     */
    public function incrementShareViews(): void
    {
        $this->increment('share_views_count');
    }
}
