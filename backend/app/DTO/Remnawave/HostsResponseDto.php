<?php

namespace App\DTO\Remnawave;

class HostsResponseDto
{
    /**
     * @param HostDto[] $response
     */
    public function __construct(
        public array $response
    ) {}

    /**
     * @return HostDto[]
     */
    public function getActiveHosts(): array
    {
        return array_filter($this->response, fn(HostDto $host) => !$host->isDisabled);
    }

    public static function fromArray(array $data): self
    {
        $hosts = array_map(
            fn(array $hostData) => HostDto::fromArray($hostData),
            $data['response'] ?? []
        );

        return new self(response: $hosts);
    }
}
