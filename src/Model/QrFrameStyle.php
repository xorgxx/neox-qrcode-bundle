<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;

final readonly class QrFrameStyle
{
    public function __construct(
        public FrameShape $shape = FrameShape::None,
        public ?string $label = null,
        public ?string $labelColor = null,
        public ?string $frameColor = null,
        public bool $decorative = true,
        public float $decorativeOpacity = 0.6,
    ) {
        if ($labelColor !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $labelColor)) {
            throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $labelColor));
        }
        if ($frameColor !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $frameColor)) {
            throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $frameColor));
        }

        if ($label !== null && mb_strlen($label) > 60) {
            throw new \InvalidArgumentException('Frame label must be 60 characters or fewer.');
        }
    }
}
