<?php

namespace App\Services\VpnKey;

use App\Models\Server;
use App\Services\Marzban\MarzbanService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class VpnKeyService
{
    private MarzbanService $marzbanService;

    public function __construct(MarzbanService $marzbanService)
    {
        $this->marzbanService = $marzbanService;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function createUserToMarzban(
        Server $server,
        string $uuid,
        string $username,
        int    $endDatetime
    ): void
    {

        $vlessConfig = ["id" => $uuid];

        if (!empty($server->flow)) {
            $vlessConfig["flow"] = $server->flow;
        }

        $data = [
            "username" => $username,
            "proxies" => [
                "vless" => $vlessConfig
            ],
            "inbounds" => [
                "vless" => [
                    "VLESS TCP REALITY"
                ]
            ],
            "expire" => $endDatetime,
            "data_limit" => 0,
            "data_limit_reset_strategy" => "no_reset",
            "status" => "active"
        ];

        try {
            $response = $this->marzbanService->addUser(
                $server,
                $data
            );

            if ($response->getStatusCode() !== 200) {
                throw new Exception(
                    "Не удалось создать пользователя в Marzban. HTTP статус: "
                    . $response->getStatusCode()
                );
            }
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();

            throw new Exception(
                "Ошибка HTTP запроса к Marzban. HTTP статус: $statusCode. Ответ: $responseBody"
            );
        }
    }

    /**
     * @throws GuzzleException
     */
    public function getUserFromMarzban(Server $server, string $username): ?array
    {
        try {
            $response = $this->marzbanService->getUser(
                $server,
                $username
            );

            if ($response->getStatusCode() === 200) {
                $body = $response->getBody()->getContents();

                return json_decode($body, true);
            }

        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }

        return null;
    }

    /**
     * @throws GuzzleException
     */
    public function deleteUserFromMarzban(Server $server, string $username): void
    {
        $this->marzbanService->deleteUser(
            $server,
            $username
        );
    }

    /**
     * @throws GuzzleException
     */
    public function getUsers(Server $server): ResponseInterface
    {
        return $this->marzbanService->getUsers($server);
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function updateUserFromMarzban(
        Server $server,
        string $uuid,
        string $username,
        int    $endDatetime,
        string $status,
        bool $flowForce = false
    ): void
    {
        $vlessConfig = ["id" => $uuid];

        if (!empty($server->flow)) {
            $vlessConfig["flow"] = $server->flow;
        }

        $data = [
            "username" => $username,
            "proxies" => [
                "vless" => $vlessConfig
            ],
            "inbounds" => [
                "vless" => [
                    "VLESS TCP REALITY"
                ]
            ],
            "expire" => $endDatetime,
            "data_limit" => 0,
            "data_limit_reset_strategy" => "no_reset",
        ];

        if (!$flowForce) {
            $data["status"] = $status;
        }

        try {
            $response = $this->marzbanService->updateUser(
                $server,
                $username,
                $data
            );

            if ($response->getStatusCode() !== 200) {
                throw new Exception(
                    "Не удалось обновить пользователя в Marzban. HTTP статус: "
                    . $response->getStatusCode()
                );
            }
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 'unknown';
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new Exception(
                "Ошибка HTTP запроса к Marzban. HTTP статус: $statusCode. Ответ: $responseBody"
            );
        }
    }
}
