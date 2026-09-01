<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class NeoxQrCodeBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
