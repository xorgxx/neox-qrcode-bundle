<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Enum;

/** Functional outer-frame shapes that preserve finder recognition. */
enum FinderFrameShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Circle = 'circle';
}
