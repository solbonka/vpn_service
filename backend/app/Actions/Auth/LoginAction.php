<?php

namespace App\Actions\Auth;

use App\Dto\Actions\Auth\LoginDto;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Client;
use Throwable;

class LoginAction
{
    /**
     * @throws ConnectionException
     * @throws Throwable
     */
    public function execute(LoginDto $dto): null|array
    {
        $user = User::query()->where('email', $dto->email)->first();

        if (! $user) {
            return null;
        }

        $passwordGrantClient = Client::query()->where('password_client', true)->first();

        return Http::asForm()
            ->throw()
            ->post('http://nginx/oauth/token', [
            'grant_type' => 'password',
            'client_id' => $passwordGrantClient->id,
            'client_secret' => $passwordGrantClient->secret,
            'username' => $dto->email,
            'password' => $dto->password,
            'scope' => '*',
        ])->json();
    }
}
