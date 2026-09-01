<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

final readonly class QrCodeResult
{
    public function __construct(
        public string $svg,
        public QrMatrix $matrix,
        public string $content,
    ) {
    }

    public function dataUri(): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->svg);
    }
}
