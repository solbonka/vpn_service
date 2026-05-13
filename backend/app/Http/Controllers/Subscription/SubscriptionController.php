<?php

namespace App\Http\Controllers\Subscription;

use App\Jobs\ConnectionMessage\SendConnectionMessageJob;
use App\Models\ClientApp;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionLinkService;
use App\Services\Subscription\SubscriptionMetaDataService;
use App\Services\Subscription\SubscriptionVpnKeyService;
use App\Traits\Error\ErrorMonitoringTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;

class SubscriptionController
{
    use ErrorMonitoringTrait;

    public function __construct(
        private readonly SubscriptionLinkService $linkService,
        private readonly SubscriptionMetaDataService $metaDataService,
        private readonly SubscriptionVpnKeyService $vpnKeyService
    ) {}


    public function handleDirectConnection(Subscription $subscription, ClientApp $client): RedirectResponse
    {
        try {
            $appOpenLink = $this->linkService->generateAppLink($subscription->token, $subscription->telegraph_chat_id, $client);

            return redirect()->away($appOpenLink);
        } catch (\Exception $e) {
            Log::error('Ошибка при прямом подключении', [
                'token' => $subscription->token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->reportClientAppError($e, $subscription, $client, 'handleDirectConnection');

            return redirect()->back()->with('error', 'Произошла ошибка при подключении. Пожалуйста, попробуйте позже.');
        }
    }


    public function fetchSubscriptionKeys(Subscription $subscription, Request $request): Response
    {
        try {
            $vlessLinks = $this->vpnKeyService->prepareVpnKeys($subscription);
            $responseText = implode("\n", $vlessLinks);
            $base64Response = base64_encode($responseText);

            $metaData = $this->metaDataService->generateMetaData($subscription, $request);

            $response = response($base64Response, 200)
                ->header('Content-Type', 'text/plain')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

            foreach ($metaData as $key => $value) {
                $response->header($key, $value);
            }

            Log::info('Отправка ответа API с ключами', [
                'subscription_id' => $subscription->id,
                'key_count' => count($vlessLinks),
                'meta_data' => $metaData
            ]);

            if ($subscription->created_at->diffInMinutes(now()) < 30) {
                SendConnectionMessageJob::dispatch($subscription->telegraphChat()->first())
                    ->delay(now()->addSeconds(20));
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Ошибка при получении ключей по API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->reportSubscriptionError($e, $subscription, 'fetchSubscriptionKeys');

            return response('Ошибка сервера при получении ключей', 500);
        }
    }
}
