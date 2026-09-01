<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum GradientType: string
{
    case None = 'none';
    case Linear = 'linear';
    case Radial = 'radial';
}
