<?php

namespace App\Providers;

use App\Services\Marzban\MarzbanClient;
use App\Services\Marzban\MarzbanService;
use App\Services\Payment\PaymentService;
use App\Services\Remnawave\RemnawaveClient;
use App\Services\Remnawave\RemnawaveService;
use App\Services\Telegram\ErrorNotificationService;
use App\Services\VpnProvider\VpnProviderFactory;
use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use YooKassa\Client;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPaymentServices();
        $this->registerMarzbanServices();
        $this->registerRemnawaveServices();
        $this->registerVpnProviderServices();

        $this->enablePasswordGrant();
    }

    public function boot(): void
    {
        Passport::tokensExpireIn(CarbonInterval::minute(60));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }

    private function registerPaymentServices(): void
    {
        $this->app->singleton(Client::class, function ($app) {
            $client = new Client();
            $client->setAuth(
                config('payment.yookassa.shopId'),
                config('payment.yookassa.secretKey')
            );
            return $client;
        });

        $this->app->singleton(PaymentService::class, function ($app) {
            return new PaymentService(
                $app->make(Client::class),
                $app->make(ErrorNotificationService::class),
            );
        });
    }

    private function registerMarzbanServices(): void
    {
        $this->app->singleton(MarzbanClient::class, function ($app) {
            return new MarzbanClient();
        });

        $this->app->singleton(MarzbanService::class, function ($app) {
            return new MarzbanService($app->make(MarzbanClient::class));
        });
    }

    private function registerRemnawaveServices(): void
    {
        $this->app->singleton(RemnawaveClient::class, function ($app) {
            return new RemnawaveClient();
        });

        $this->app->singleton(RemnawaveService::class, function ($app) {
            return new RemnawaveService($app->make(RemnawaveClient::class));
        });
    }

    private function registerVpnProviderServices(): void
    {
        $this->app->singleton(VpnProviderFactory::class, function ($app) {
            return new VpnProviderFactory(
                $app->make(MarzbanService::class),
                $app->make(RemnawaveService::class)
            );
        });
    }

    private function enablePasswordGrant(): void
    {
        Passport::enablePasswordGrant();
    }
}
