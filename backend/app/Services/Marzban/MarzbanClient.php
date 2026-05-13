<?php

namespace App\Services\Marzban;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;

class MarzbanClient
{
    private Client $httpClient;
    private const TOKEN_CACHE_TTL_HOURS = 23;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function authenticate(
        string $baseUrl,
        string $username,
        string $password,
        int $serverId
    ): string {
        $cacheKey = "marzban_token_$serverId";
        $cachedToken = Cache::get($cacheKey);

        if ($cachedToken && $this->validateToken($baseUrl, $cachedToken)) {
            return $cachedToken;
        }

        $response = $this->httpClient->post($baseUrl . '/api/admin/token', [
            'form_params' => [
                'username' => $username,
                'password' => $password
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $token = $data['access_token'];

        Cache::put($cacheKey, $token, now()->addHours(self::TOKEN_CACHE_TTL_HOURS));

        return $token;
    }

    /**
     * @throws GuzzleException
     */
    private function validateToken(string $baseUrl, string $token): bool
    {
        try {
            $response = $this->httpClient->get($baseUrl . '/api/admin', [
                'headers' => ['Authorization' => 'Bearer ' . $token]
            ]);

            return $response->getStatusCode() === 200;
        } catch (ConnectException $e) {
            return false;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function makeRequest(
        string $method,
        string $url,
        ?string $token = null,
        array $data = [],
        array $headers = []
    ): ResponseInterface
    {
        $options = ['json' => $data];

        if ($token) {
            $options['headers']['Authorization'] = 'Bearer ' . $token;
        }

        $options['headers'] = array_merge($options['headers'] ?? [], $headers);

        return $this->httpClient->request($method, $url, $options);
    }
}
