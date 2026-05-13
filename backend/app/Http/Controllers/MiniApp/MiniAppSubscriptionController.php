<?php

namespace App\Http\Controllers\MiniApp;

use App\DTO\Payment\PaymentCreationData;
use App\Helpers\PricingHelper;
use App\Http\Controllers\Controller;
use App\Models\Duration;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\Payment\PaymentService;
use App\Services\PromoCode\PromoCodeService;
use App\Services\Subscription\SubscriptionService;
use App\Services\SyncVpnKey\SyncVpnKeyService;
use App\Services\Remnawave\SyncVpnKeyService as RemnawaveSyncVpnKeyService;
use App\Enums\Subscription\SubscriptionStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use YooKassa\Client;

class MiniAppSubscriptionController extends Controller
{
    private PaymentService $paymentService;
    private SubscriptionService $subscriptionService;
    private SyncVpnKeyService $syncVpnKeyService;
    private PromoCodeService $promoCodeService;

    public function __construct(
        PaymentService $paymentService,
        SubscriptionService $subscriptionService,
        SyncVpnKeyService $syncVpnKeyService,
        PromoCodeService $promoCodeService
    ) {
        $this->paymentService = $paymentService;
        $this->subscriptionService = $subscriptionService;
        $this->syncVpnKeyService = $syncVpnKeyService;
        $this->promoCodeService = $promoCodeService;
    }


    public function getPlans()
    {
        $isPaymentEnabled = config('payment.enabled');

        $plans = Plan::paidWithActiveServers()->with('servers')->get();
        $durations = Duration::query()->where('is_trial', false)->get();

        $plansData = $plans->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'price' => $plan->price,
                'servers_count' => $plan->servers->count(),
                'description' => "План {$plan->name} с доступом к {$plan->servers->count()} серверам"
            ];
        });

        $durationsData = $durations->map(function ($duration) {
            return [
                'id' => $duration->id,
                'name' => $duration->name,
                'days' => $duration->days,
                'discount_percentage' => $duration->discount_percentage,
                'is_trial' => $duration->is_trial
            ];
        });

        return response()->json([
            'success' => true,
            'payment_enabled' => $isPaymentEnabled,
            'plans' => $plansData,
            'durations' => $durationsData,
            'has_multiple_plans' => $plans->count() > 1
        ]);
    }

    public function calculatePrice(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'duration_id' => 'required|integer|exists:durations,id'
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $duration = Duration::findOrFail($request->duration_id);

        $calculated = PricingHelper::calculateDiscountedPrice(
            $plan->price,
            $duration->days,
            $duration->discount_percentage
        );

        return response()->json([
            'success' => true,
            'plan_id' => $plan->id,
            'duration_id' => $duration->id,
            'old_price' => $calculated['oldPrice'],
            'discounted_price' => $calculated['discountedPrice'],
            'discount_percent' => $calculated['discountPercent'],
            'plan_name' => $plan->name,
            'duration_name' => $duration->name
        ]);
    }

    public function createPayment(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'duration_id' => 'required|integer|exists:durations,id',
            'promo_code' => 'nullable|string|max:20'
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        $duration = Duration::findOrFail($request->duration_id);

        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        $calculated = PricingHelper::calculateDiscountedPrice(
            $plan->price,
            $duration->days,
            $duration->discount_percentage
        );

        $finalAmount = $calculated['discountedPrice'];
        $promoCodeId = null;
        $promoCodeDiscount = null;

        // Применяем промокод если есть
        if ($request->filled('promo_code')) {
            $validationResult = $this->promoCodeService->validatePromoCode(
                $request->promo_code,
                $subscription->id,
                $duration->id
            );

            if (!$validationResult['valid']) {
                return response()->json([
                    'success' => false,
                    'error' => $validationResult['error']
                ], 422);
            }

            $promoCode = $validationResult['promo_code'];
            $promoCodeId = $promoCode->id;
            
            // Рассчитываем скидку от промокода
            $promoDiscount = $this->promoCodeService->calculateDiscount(
                $promoCode,
                $finalAmount
            );
            
            $finalAmount = $promoDiscount['final_amount'];
            $promoCodeDiscount = $promoDiscount;
        }

        try {
            $paymentData = new PaymentCreationData(
                $plan,
                $duration,
                $finalAmount,
                $subscription->telegraph_chat_id
            );

            $payment = $this->paymentService->createPayment($paymentData);

            // Сохраняем promo_code_id в платеже если был использован промокод
            if ($promoCodeId) {
                $paymentModel = Payment::where('yookassa_payment_id', $payment['id'])->first();
                if ($paymentModel) {
                    $paymentModel->update(['promo_code_id' => $promoCodeId]);
                    
                    // Применяем промокод (увеличиваем счетчик, сохраняем использование)
                    $this->promoCodeService->applyPromoCode(
                        $validationResult['promo_code'],
                        $subscription->id,
                        $promoCodeDiscount['original_amount'],
                        $paymentModel->id
                    );
                }
            }

            $response = [
                'success' => true,
                'payment_url' => $payment['payment_url'] ?? '',
                'payment_id' => $payment['id'] ?? '',
                'amount' => $finalAmount
            ];

            if ($promoCodeDiscount) {
                $response['promo_code_applied'] = true;
                $response['original_amount'] = $promoCodeDiscount['original_amount'];
                $response['discount_amount'] = $promoCodeDiscount['discount_amount'];
                $response['discount_percent'] = $promoCodeDiscount['discount_percent'];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Failed to create payment for Mini App', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
                'plan_id' => $plan->id,
                'duration_id' => $duration->id,
                'promo_code' => $request->promo_code ?? null
            ]);

            return response()->json(['error' => 'Failed to create payment'], 500);
        }
    }
    public function activateSubscription(Request $request)
    {
        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        if ($subscription->status !== SubscriptionStatusEnum::BLOCKED) {
            return response()->json([
                'success' => false,
                'message' => 'Ваша подписка еще АКТИВНА, продление будет доступно после истечения срока подписки!'
            ]);
        }

        try {
            $subscriptionService = app(SubscriptionService::class, ['chat' => $subscription->telegraphChat]);
            $subscriptionUpdated = $subscriptionService->update($subscription);

            if (!$subscriptionUpdated) {
                Log::error("Не удалось активировать подписку для чата: {$subscription->telegraph_chat_id}");
                return response()->json(['error' => 'Failed to activate subscription'], 500);
            }

            $servers = app(SyncVpnKeyService::class, ['subscription' => $subscriptionUpdated])->handle();
            app(RemnawaveSyncVpnKeyService::class)->handle($subscriptionUpdated);

            if (!$servers) {
                Log::error("Не удалось активировать VPN ключи для чата: {$subscription->telegraph_chat_id}");
                return response()->json(['error' => 'Failed to sync VPN keys'], 500);
            }

            Log::info("Marzban VPN ключи активированы для чата: {$subscription->telegraph_chat_id}");
            Log::info("Remnawave VPN ключи активированы для чата: {$subscription->telegraph_chat_id}");


            return response()->json([
                'success' => true,
                'message' => 'Ваша подписка активирована.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to activate subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id
            ]);

            return response()->json(['error' => 'Failed to activate subscription'], 500);
        }
    }
}
