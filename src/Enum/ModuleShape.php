<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

enum ModuleShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Dot = 'dot';
    case Diamond = 'diamond';
    case Heart = 'heart';
    case Liquid = 'liquid';
}
