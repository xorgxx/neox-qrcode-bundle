<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum ReadabilityProfile: string
{
    case Compact = 'compact';
    case Balanced = 'balanced';
    case Print = 'print';

    /** @return list<int> */
    public function sizes(): array
    {
        return match ($this) {
            self::Compact => [96, 128, 256],
            self::Balanced => [128, 256, 512],
            self::Print => [256, 512, 1024],
        };
    }
}
