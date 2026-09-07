<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class QrValidationReport
{
    /**
     * @param list<string>                              $errors
     * @param list<string>                              $warnings
     * @param array<string, int|float|string|bool|null> $readabilityDetails
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
        public array $warnings = [],
        public float $contrastRatio = 0.0,
        public int $readabilityScore = 0,
        public array $readabilityDetails = [],
    ) {
    }
}
