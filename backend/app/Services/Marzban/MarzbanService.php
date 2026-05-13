<?php

namespace App\Services\Marzban;

use App\Models\Server;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class MarzbanService
{
    private MarzbanClient $client;

    public function __construct(MarzbanClient $client)
    {
        $this->client = $client;
    }

    /**
     * @throws GuzzleException
     */
    public function getUser(Server $server, string $username): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'GET',
            $server->base_url . '/api/user/' . $username,
            $token
        );
    }

    /**
     * @throws GuzzleException
     */
    public function addUser(Server $server, array $data): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'POST',
            $server->base_url . '/api/user',
            $token,
            $data
        );
    }


    /**
     * @throws GuzzleException
     */
    public function updateUser(Server $server, string $username, array $data): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'PUT',
            $server->base_url . '/api/user/' . $username,
            $token,
            $data
        );
    }

    /**
     * @throws GuzzleException
     */
    public function deleteUser(Server $server, string $username): void
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        $this->client->makeRequest(
            'DELETE',
            $server->base_url . '/api/user/' . $username,
            $token
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getServerStatistics(Server $server): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'GET',
            $server->base_url . '/api/system',
            $token
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getNodesStatistics(Server $server): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'GET',
            $server->base_url . '/api/nodes',
            $token
        );
    }

    public function getUsers(Server $server): ResponseInterface
    {
        $token = $this->client->authenticate(
            $server->base_url,
            $server->login,
            $server->password,
            $server->id
        );

        return $this->client->makeRequest(
            'GET',
            $server->base_url . '/api/users',
            $token
        );
    }
}
