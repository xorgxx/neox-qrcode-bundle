<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;

final readonly class QrStyle
{
    public function __construct(
        public int $size = 320,
        public int $margin = 4,
        public ModuleShape $moduleShape = ModuleShape::Square,
        public FinderShape $finderShape = FinderShape::Square,
        public string $foreground = '#111111',
        public string $background = '#ffffff',
        public ?string $finderColor = null,
        public float $moduleScale = 0.92,
        public GradientType $gradientType = GradientType::None,
        public ?string $gradientTo = null,
        public ?string $logoHref = null,
        public float $logoScale = 0.20,
        public bool $logoBackground = true,
        public AlignmentShape $alignmentShape = AlignmentShape::Square,
        public ?string $alignmentColor = null,
        public ?string $finderIconHref = null,
        public float $finderIconScale = 0.6,
        public FinderEffect $finderEffect = FinderEffect::None,
        public ?string $finderGradientTo = null,
        public ?ModuleShape $finderCenterShape = null,
        public ?FinderShape $finderEyeShape = null,
    ) {
        if ($size < 64 || $size > 4096) {
            throw new \InvalidArgumentException('QR size must be between 64 and 4096 pixels.');
        }
        if ($margin < 0 || $margin > 32) {
            throw new \InvalidArgumentException('QR margin must be between 0 and 32 modules.');
        }
        if ($moduleScale <= 0.2 || $moduleScale > 1.0) {
            throw new \InvalidArgumentException('moduleScale must be > 0.2 and <= 1.0.');
        }
        if ($logoScale < 0.08 || $logoScale > 0.30) {
            throw new \InvalidArgumentException('logoScale must be between 0.08 and 0.30.');
        }
        if ($finderIconScale < 0.2 || $finderIconScale > 0.85) {
            throw new \InvalidArgumentException('finderIconScale must be between 0.2 and 0.85.');
        }

        foreach ([$foreground, $background, $finderColor, $gradientTo, $alignmentColor, $finderGradientTo] as $color) {
            if ($color !== null && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $color));
            }
        }

        if ($gradientType !== GradientType::None && $gradientTo === null) {
            throw new \InvalidArgumentException('gradientTo is required when a gradient is enabled.');
        }

        if ($finderEffect === FinderEffect::Gradient && $finderGradientTo === null) {
            throw new \InvalidArgumentException('finderGradientTo is required when finderEffect is gradient.');
        }

        if ($logoHref !== null && !$this->isSafeImageHref($logoHref)) {
            throw new \InvalidArgumentException('logoHref must be an application-relative URL (/...) or an image data URI.');
        }

        if ($finderIconHref !== null && !$this->isSafeImageHref($finderIconHref)) {
            throw new \InvalidArgumentException('finderIconHref must be an application-relative URL (/...) or an image data URI.');
        }
    }

    private function isSafeImageHref(string $href): bool
    {
        return str_starts_with($href, '/')
            || preg_match('#^data:image/(png|jpeg|webp|gif|svg\+xml);#i', $href) === 1;
    }
}
