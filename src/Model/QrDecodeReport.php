<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class QrDecodeReport
{
    /** @param list<int> $successfulSizes */
    public function __construct(
        public bool $available,
        public array $successfulSizes = [],
        public ?string $decodedContent = null,
        public ?string $message = null,
    ) {
    }
}
