<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Model;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEyeShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderFrameShape;
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
        public FinderShape|FinderEyeShape|null $finderEyeShape = null,
        public ?FinderFrameShape $finderFrameShape = null,
        public float $finderEyeScale = 1.0,
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
        if ($finderEyeScale < 0.85 || $finderEyeScale > 1.08) {
            throw new \InvalidArgumentException('finderEyeScale must be between 0.85 and 1.08.');
        }

        foreach ([$foreground, $finderColor, $gradientTo, $alignmentColor, $finderGradientTo] as $color) {
            if (null !== $color && !preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
                throw new \InvalidArgumentException(sprintf('Invalid color "%s".', $color));
            }
        }

        if ('transparent' !== $background && !preg_match('/^#[0-9a-fA-F]{6}$/', $background)) {
            throw new \InvalidArgumentException(sprintf('Invalid color "%s"; use a #rrggbb color or "transparent".', $background));
        }

        if (GradientType::None !== $gradientType && null === $gradientTo) {
            throw new \InvalidArgumentException('gradientTo is required when a gradient is enabled.');
        }

        if (FinderEffect::Gradient === $finderEffect && null === $finderGradientTo) {
            throw new \InvalidArgumentException('finderGradientTo is required when finderEffect is gradient.');
        }

        if (null !== $logoHref && !$this->isSafeImageHref($logoHref)) {
            throw new \InvalidArgumentException('logoHref must be an application-relative URL (/...) or an image data URI.');
        }

        if (null !== $finderIconHref && !$this->isSafeImageHref($finderIconHref)) {
            throw new \InvalidArgumentException('finderIconHref must be an application-relative URL (/...) or an image data URI.');
        }
    }

    private function isSafeImageHref(string $href): bool
    {
        return str_starts_with($href, '/')
            || 1 === preg_match('#^data:image/(png|jpeg|webp|gif|svg\+xml);#i', $href);
    }

    /**
     * Builds a style from the public option names shared by the HTTP API,
     * user presets and the Twig component, so hydration lives in one place.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $background = (string) ($data['background'] ?? '#ffffff');
        if ('transparent' === $background || self::boolValue($data['transparent'] ?? false)) {
            $background = 'transparent';
        }

        return new self(
            size: (int) ($data['size'] ?? 320),
            margin: (int) ($data['margin'] ?? 4),
            moduleShape: ModuleShape::from((string) ($data['moduleShape'] ?? 'square')),
            finderShape: FinderShape::from((string) ($data['finderShape'] ?? 'square')),
            foreground: (string) ($data['foreground'] ?? '#111111'),
            background: $background,
            finderColor: isset($data['finderColor']) ? (string) $data['finderColor'] : null,
            moduleScale: (float) ($data['moduleScale'] ?? 0.92),
            gradientType: GradientType::from((string) ($data['gradientType'] ?? 'none')),
            gradientTo: isset($data['gradientTo']) ? (string) $data['gradientTo'] : null,
            logoHref: isset($data['logoHref']) && '' !== $data['logoHref'] ? (string) $data['logoHref'] : null,
            logoScale: (float) ($data['logoScale'] ?? 0.20),
            logoBackground: self::boolValue($data['logoBackground'] ?? true),
            alignmentShape: AlignmentShape::from((string) ($data['alignmentShape'] ?? 'square')),
            alignmentColor: isset($data['alignmentColor']) ? (string) $data['alignmentColor'] : null,
            finderIconHref: isset($data['finderIconHref']) && '' !== $data['finderIconHref'] ? (string) $data['finderIconHref'] : null,
            finderIconScale: (float) ($data['finderIconScale'] ?? 0.6),
            finderEffect: FinderEffect::from((string) ($data['finderEffect'] ?? 'none')),
            finderGradientTo: isset($data['finderGradientTo']) ? (string) $data['finderGradientTo'] : null,
            finderCenterShape: isset($data['finderCenterShape']) && '' !== $data['finderCenterShape']
                ? ModuleShape::from((string) $data['finderCenterShape'])
                : null,
            finderEyeShape: isset($data['finderEyeShape']) && '' !== $data['finderEyeShape']
                ? FinderEyeShape::from((string) $data['finderEyeShape'])
                : null,
            finderFrameShape: isset($data['finderFrameShape']) && '' !== $data['finderFrameShape']
                ? FinderFrameShape::from((string) $data['finderFrameShape'])
                : null,
            finderEyeScale: (float) ($data['finderEyeScale'] ?? 1.0),
        );
    }

    /**
     * Booleans arrive as real bools from JSON but as strings from form data;
     * a plain (bool) cast would turn "false" into true.
     */
    private static function boolValue(mixed $value): bool
    {
        $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if (null === $filtered) {
            throw new \InvalidArgumentException(sprintf('Invalid boolean value "%s".', (string) $value));
        }

        return $filtered;
    }
}
