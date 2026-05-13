<?php

namespace App\Services\Remnawave;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class RemnawaveClient
{
    private Client $httpClient;
    private string $apiToken;
    private string $baseUrl;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);

        $this->apiToken = config('vpn.remnawave.api_token');
        $this->baseUrl = config('vpn.remnawave.base_url');
    }

    /**
     * @throws GuzzleException
     */
    public function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): ResponseInterface
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $options = [
            'headers' => array_merge([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ], $headers)
        ];

        if (!empty($data)) {
            $options['json'] = $data;
        }

        return $this->httpClient->request($method, $url, $options);
    }
}
