<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class QrValidationReport
{
    /** @param list<string> $errors @param list<string> $warnings */
    public function __construct(
        public bool $valid,
        public array $errors = [],
        public array $warnings = [],
        public float $contrastRatio = 0.0,
    ) {
    }
}
