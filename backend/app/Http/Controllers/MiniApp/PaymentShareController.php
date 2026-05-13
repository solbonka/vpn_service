<?php

namespace App\Http\Controllers\MiniApp;

use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentShareService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentShareController extends Controller
{
    public function __construct(
        private readonly PaymentShareService $shareService
    ) {}

    /**
     * Создать share-токен для платежа
     *
     * POST /miniapp/payment/create-share-link
     * Body: { payment_id: string (yookassa_payment_id) }
     * Headers: X-Sub-Token
     */
    public function createShareLink(Request $request): JsonResponse
    {
        $request->validate([
            'payment_id' => 'required|string|exists:payments,yookassa_payment_id'
        ]);

        $subscription = $request->attributes->get('subscription');

        if (!$subscription) {
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        try {
            $payment = \App\Models\Payment::where('yookassa_payment_id', $request->payment_id)
                ->firstOrFail();

            if ($payment->subscription_id !== $subscription->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment does not belong to this subscription'
                ], 403);
            }

            $result = $this->shareService->createShareLinkForPayment($payment->id);

            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Failed to create share link', [
                'error' => $e->getMessage(),
                'yookassa_payment_id' => $request->payment_id,
                'subscription_id' => $subscription->id
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     *
     * GET /api/payment/share/{token}
     */
    public function getShareLinkInfo(string $token): JsonResponse
    {
        try {
            $info = $this->shareService->getPaymentByShareToken($token);

            return response()->json([
                'success' => true,
                'data' => $info
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get share link info', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Share link not found or invalid'
            ], 404);
        }
    }
}


