<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

/** Decorative shapes allowed for the 3x3 center of a finder pattern. */
enum FinderEyeShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Circle = 'circle';
    case Diamond = 'diamond';
    case Leaf = 'leaf';
    case Hexagon = 'hexagon';
    case Star = 'star';
    case Octagon = 'octagon';
    case Shield = 'shield';
    case Heart = 'heart';
    case Flower = 'flower';
}
