<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum FrameShape: string
{
    case None = 'none';
    case Circle = 'circle';
    case RoundedSquare = 'rounded_square';
    case Heart = 'heart';
    case Star = 'star';
    case Hexagon = 'hexagon';
    case Security = 'security';
}
