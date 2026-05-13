<?php

namespace App\Actions\Auth;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use Throwable;

class RefreshTokenAction
{
    /**
     * @throws ConnectionException
     * @throws Throwable
     */
    public function execute(?string $refreshToken)
    {
        $passwordGrantClient = Client::query()->where('password_client', true)->first();

        $response = Http::asForm()->post('http://nginx/oauth/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $passwordGrantClient->id,
            'client_secret' => $passwordGrantClient->secret,
            'scope' => '*',
        ]);

        return $response->json();
    }
}
