<?php

use App\Http\Controllers\Admin\User\AuthController;
use App\Http\Controllers\Admin\MiniappSettingsController;
use App\Http\Controllers\Admin\Referral\ReferralSettingsController;
use App\Http\Controllers\MiniApp\MiniAppAuthController;
use App\Http\Controllers\MiniApp\MiniAppConnectController;
use App\Http\Controllers\MiniApp\MiniAppSubscriptionController;
use App\Http\Controllers\MiniApp\PaymentShareController;
use App\Http\Controllers\MiniApp\ReferralController;
use App\Http\Controllers\MiniApp\LotteryController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Web\WebAuthController;
use App\Http\Controllers\Web\WebSubscriptionController;
use App\Http\Middleware\InjectSubscription;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Middleware\EnsureSubscriptionIsActive;
use App\Models\MiniappSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middleware' => [
        EnsureSubscriptionIsActive::class
    ],
], function () {
    Route::get('/sub/{subscription:token}', [SubscriptionController::class, 'fetchSubscriptionKeys'])
        ->name('subscription.keys');
    Route::get('/subscription/{subscription:token}/happ', [SubscriptionController::class, 'fetchSubscriptionKeys']);
});

Route::match(['GET', 'POST'], '/payments/callback', [PaymentController::class, 'callback']);

Route::get('/payment/share/{token}', [PaymentShareController::class, 'getShareLinkInfo']);

Route::prefix('miniapp')->group(function () {
    Route::post('/auth', [MiniAppAuthController::class, 'authenticate']);

    Route::get('/logo', function () {
        $logo = MiniappSettings::getLogo();
        if ($logo) {
            return response()->json([
                'success' => true,
                'logo' => $logo
            ]);
        }
        return response()->json(['success' => false], 404);
    });

    Route::get('/lottery-image', function () {
        $lotteryImage = MiniappSettings::getLotteryImage();
        if ($lotteryImage) {
            return response()->json([
                'success' => true,
                'lottery_image' => $lotteryImage
            ]);
        }
        return response()->json(['success' => false], 404);
    });

    Route::get('/subscription/plans', [MiniAppSubscriptionController::class, 'getPlans']);
    Route::post('/subscription/calculate-price', [MiniAppSubscriptionController::class, 'calculatePrice']);

    Route::middleware(InjectSubscription::class)->group(function () {
        Route::get('/connect/{os}', [MiniAppConnectController::class, 'getConnectInfo']);

        Route::get('/subscription/', [MiniAppAuthController::class, 'getSubscription']);
        Route::post('/subscription/create-payment', [MiniAppSubscriptionController::class, 'createPayment']);
        Route::post('/subscription/activate', [MiniAppSubscriptionController::class, 'activateSubscription']);

        Route::prefix('referral')->group(function () {
            Route::get('/info', [ReferralController::class, 'info']);
            Route::get('/stats', [ReferralController::class, 'stats']);
            Route::post('/process', [ReferralController::class, 'processReferral']);
        });

        Route::prefix('lottery')->group(function () {
            Route::get('/info', [LotteryController::class, 'info']);
            Route::get('/tickets', [LotteryController::class, 'tickets']);
            Route::get('/leaderboard', [LotteryController::class, 'leaderboard']);
            Route::get('/available-numbers', [LotteryController::class, 'getAvailableNumbers']);
            Route::post('/check-number', [LotteryController::class, 'checkNumberAvailability']);
            Route::post('/change-number-payment', [LotteryController::class, 'createNumberChangePayment']);
        });

        Route::prefix('promo-code')->group(function () {
            Route::post('/validate', [\App\Http\Controllers\MiniApp\PromoCodeController::class, 'validate']);
            Route::post('/calculate', [\App\Http\Controllers\MiniApp\PromoCodeController::class, 'calculate']);
        });

        Route::post('/payment/create-share-link', [PaymentShareController::class, 'createShareLink']);
    });
});

Route::prefix('web')->group(function () {
    Route::get('/subscription/plans', [WebSubscriptionController::class, 'getPlans']);

    Route::prefix('auth')->group(function () {
        Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:10,1');
        Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:20,1');
        Route::post('/telegram', [WebAuthController::class, 'telegramLogin'])->middleware('throttle:30,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [WebAuthController::class, 'me']);
            Route::post('/logout', [WebAuthController::class, 'logout']);
        });
    });
});

Route::group([
    'prefix' => 'admin',
], function () {

    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'store']);
        Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth:api');
    });

    Route::group([
        'middleware' => ['auth:api'],
    ], function () {
        require_once base_path('routes/admin/servers/servers.php');
        require_once base_path('routes/admin/metrics/metrics.php');
        require_once base_path('routes/admin/chats/chats.php');
        require_once base_path('routes/admin/promocodes/promocodes.php');

        Route::prefix('miniapp-settings')->group(function () {
            Route::get('/', [MiniappSettingsController::class, 'index']);
            Route::put('/logo', [MiniappSettingsController::class, 'updateLogo']);
            Route::delete('/logo', [MiniappSettingsController::class, 'deleteLogo']);
            Route::put('/lottery-image', [MiniappSettingsController::class, 'updateLotteryImage']);
            Route::delete('/lottery-image', [MiniappSettingsController::class, 'deleteLotteryImage']);
        });

        Route::prefix('referral-settings')->group(function () {
            Route::get('/', [ReferralSettingsController::class, 'index']);
            Route::post('/', [ReferralSettingsController::class, 'store']);
            Route::put('/{id}', [ReferralSettingsController::class, 'update']);
            Route::delete('/{id}', [ReferralSettingsController::class, 'destroy']);
            Route::post('/{id}/activate', [ReferralSettingsController::class, 'activate']);
            Route::get('/stats', [ReferralSettingsController::class, 'stats']);
        });

        require_once base_path('routes/admin/analytics/referral.php');
    });
});
