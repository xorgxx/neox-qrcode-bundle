<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

use BaconQrCode\Common\ErrorCorrectionLevel;

/** @see https://github.com/Bacon/BaconQrCode */
enum ErrorCorrection: string
{
    case Low = 'L';
    case Medium = 'M';
    case Quartile = 'Q';
    case High = 'H';

    public function toBacon(): ErrorCorrectionLevel
    {
        return match ($this) {
            self::Low => ErrorCorrectionLevel::L(),
            self::Medium => ErrorCorrectionLevel::M(),
            self::Quartile => ErrorCorrectionLevel::Q(),
            self::High => ErrorCorrectionLevel::H(),
        };
    }
}
