<?php

namespace App\Services\Remnawave;

use App\DTO\Remnawave\HostsResponseDto;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;

class RemnawaveService
{
    public function __construct(
        private RemnawaveClient $client
    ) {}

    /**
     * @throws GuzzleException
     */
    public function getUser(string $username): ?ResponseInterface
    {
        try {
            return $this->client->makeRequest(
                'GET',
                '/api/users/by-username/' . $username
            );
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    /**
     * @throws GuzzleException
     */
    public function addUser(array $data): ResponseInterface
    {
        return $this->client->makeRequest(
            'POST',
            '/api/users',
            $data
        );
    }

    /**
     * @throws GuzzleException
     */
    public function updateUser(array $data): ResponseInterface
    {
        return $this->client->makeRequest(
            'PATCH',
            '/api/users/',
            $data
        );
    }

    /**
     * @throws GuzzleException
     */
    public function deleteUser(string $uuid): void
    {
        $this->client->makeRequest(
            'DELETE',
            '/api/users/' . $uuid
        );
    }


    /**
     * @throws GuzzleException
     */
    public function getNodesStatistics(): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/api/nodes'
        );
    }

    public function getHosts(): HostsResponseDto
    {
        try {
            $response = $this->client->makeRequest(
                'GET',
                '/api/hosts'
            );

            $data = json_decode($response->getBody()->getContents(), true);

            return HostsResponseDto::fromArray($data);
        } catch (\Exception $e) {
            Log::warning('Не удалось получить хосты Remnawave', [
                'error' => $e->getMessage()
            ]);

            return new HostsResponseDto(response: []);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getNodeStatistics(string $nodeUuid): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/api/nodes/' . $nodeUuid
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getNodesMetrics(): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/api/system/nodes/metrics'
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getNodesUsageRealtime(): ResponseInterface
    {
        return $this->client->makeRequest(
            'GET',
            '/api/nodes/usage/realtime'
        );
    }
}
