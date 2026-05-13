<?php

namespace App\DTO\Remnawave;

class HostDto
{
    public function __construct(
        public string $uuid,
        public int $viewPosition,
        public string $remark,
        public string $address,
        public int $port,
        public ?string $path,
        public ?string $sni,
        public ?string $host,
        public ?string $alpn,
        public ?string $fingerprint,
        public bool $isDisabled,
        public string $securityLayer,
        public ?array $xHttpExtraParams,
        public ?array $muxParams,
        public ?array $sockoptParams,
        public ?string $serverDescription,
        public bool $allowInsecure,
        public ?string $tag,
        public bool $isHidden,
        public bool $overrideSniFromAddress,
        public ?string $vlessRouteId,
        public bool $shuffleHost,
        public bool $mihomoX25519,
        public array $inbound,
        public array $nodes
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: $data['uuid'],
            viewPosition: $data['viewPosition'],
            remark: $data['remark'],
            address: $data['address'],
            port: $data['port'],
            path: $data['path'] ?? null,
            sni: $data['sni'] ?? null,
            host: $data['host'] ?? null,
            alpn: $data['alpn'] ?? null,
            fingerprint: $data['fingerprint'] ?? null,
            isDisabled: $data['isDisabled'],
            securityLayer: $data['securityLayer'],
            xHttpExtraParams: $data['xHttpExtraParams'] ?? null,
            muxParams: $data['muxParams'] ?? null,
            sockoptParams: $data['sockoptParams'] ?? null,
            serverDescription: $data['serverDescription'] ?? null,
            allowInsecure: $data['allowInsecure'],
            tag: $data['tag'] ?? null,
            isHidden: $data['isHidden'],
            overrideSniFromAddress: $data['overrideSniFromAddress'],
            vlessRouteId: $data['vlessRouteId'] ?? null,
            shuffleHost: $data['shuffleHost'],
            mihomoX25519: $data['mihomoX25519'],
            inbound: $data['inbound'],
            nodes: $data['nodes']
        );
    }
}
