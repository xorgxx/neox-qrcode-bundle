<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class SecureQrPayload
{
    /** @param array<string,mixed> $data */
    public function __construct(
        public array $data,
        public ?int $expiresAt = null,
        public ?string $jti = null,
        public bool $singleUse = false,
    ) {
    }
}
