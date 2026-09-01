<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum AlignmentShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Circle = 'circle';
    case Diamond = 'diamond';
    case Leaf = 'leaf';
    case Dot = 'dot';
}
