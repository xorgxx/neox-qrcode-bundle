<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum FinderShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Circle = 'circle';
    case Diamond = 'diamond';
    case Leaf = 'leaf';
    case Hexagon = 'hexagon';
    case Star = 'star';
    case Dotted = 'dotted';
    case Minimal = 'minimal';
    case Inverted = 'inverted';
}
