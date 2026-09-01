<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum FinderEffect: string
{
    case None = 'none';
    case DoubleStroke = 'double_stroke';
    case Dashed = 'dashed';
    case Shadow = 'shadow';
    case Gradient = 'gradient';
}
