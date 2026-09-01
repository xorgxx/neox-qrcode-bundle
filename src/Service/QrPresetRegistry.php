<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;

final class QrPresetRegistry
{
    /** @var array<string, array{style: QrStyle, frame?: QrFrameStyle}> */
    private array $presets;

    public function __construct()
    {
        $this->presets = [
            'classic' => ['style' => new QrStyle()],
            'dots' => ['style' => new QrStyle(moduleShape: ModuleShape::Dot, finderShape: FinderShape::Rounded, moduleScale: 0.88)],
            'rounded' => ['style' => new QrStyle(moduleShape: ModuleShape::Rounded, finderShape: FinderShape::Rounded)],
            'heart' => [
                'style' => new QrStyle(
                    size: 368,
                    margin: 1,
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Rounded,
                    foreground: '#400040',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Rounded,
                    alignmentColor: '#400040',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Heart, frameColor: '#ffffff', decorative: true),
            ],
            'gold' => ['style' => new QrStyle(moduleShape: ModuleShape::Dot, finderShape: FinderShape::Rounded, foreground: '#111111', finderColor: '#D59618')],
            //            'gradient' => ['style' => new QrStyle(moduleShape: ModuleShape::Rounded, finderShape: FinderShape::Rounded, foreground: '#111111', gradientType: GradientType::Linear, gradientTo: '#D59618')],
            //            'minimal' => ['style' => new QrStyle(moduleShape: ModuleShape::Dot, finderShape: FinderShape::Minimal, moduleScale: 0.82, foreground: '#111111')],
            //            'inverted' => ['style' => new QrStyle(moduleShape: ModuleShape::Rounded, finderShape: FinderShape::Inverted, foreground: '#111111', finderColor: '#111111')],
            //            'star' => ['style' => new QrStyle(moduleShape: ModuleShape::Diamond, finderShape: FinderShape::Star, foreground: '#111111', finderColor: '#D59618')],
            //            'outline' => ['style' => new QrStyle(moduleShape: ModuleShape::Square, finderShape: FinderShape::Rounded, foreground: '#111111', finderEffect: FinderEffect::DoubleStroke)],
            //            'stitched' => ['style' => new QrStyle(moduleShape: ModuleShape::Rounded, finderShape: FinderShape::Rounded, foreground: '#111111', finderEffect: FinderEffect::Dashed)],
            //            'floating' => ['style' => new QrStyle(moduleShape: ModuleShape::Rounded, finderShape: FinderShape::Rounded, foreground: '#111111', finderEffect: FinderEffect::Shadow)],
            'neon' => [
                'style' => new QrStyle(
                    moduleShape: ModuleShape::Dot,
                    finderShape: FinderShape::Circle,
                    foreground: '#111111',
                    gradientType: GradientType::Radial,
                    gradientTo: '#00E5C9',
                    finderColor: '#111111',
                    finderEffect: FinderEffect::Gradient,
                    finderGradientTo: '#00E5C9',
                ),
            ],
            'liquid-security' => [
                'style' => new QrStyle(
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Square,
                    foreground: '#111111',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Circle,
                    alignmentColor: '#cc2020',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Security, frameColor: '#ffffff'),
            ],
            'liquid-heart' => [
                'style' => new QrStyle(
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Rounded,
                    foreground: '#004080',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Circle,
                    alignmentColor: '#cc2020',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Heart, frameColor: '#ffffff', decorative: true),
            ],
            'liquid-hexagon' => [
                'style' => new QrStyle(
                    size: 368,
                    margin: 1,
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Rounded,
                    foreground: '#400040',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Rounded,
                    alignmentColor: '#400040',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Hexagon, frameColor: '#ffffff', decorative: true),
            ],
            'liquid-circle' => [
                'style' => new QrStyle(
                    size: 368,
                    margin: 1,
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Rounded,
                    foreground: '#400040',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Rounded,
                    alignmentColor: '#400040',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Circle, frameColor: '#ffffff', decorative: true),
            ],
            'liquid-star' => [
                'style' => new QrStyle(
                    size: 368,
                    margin: 1,
                    moduleShape: ModuleShape::Liquid,
                    finderShape: FinderShape::Rounded,
                    foreground: '#004080',
                    background: '#ffffff',
                    finderColor: '#ce0000',
                    alignmentShape: AlignmentShape::Circle,
                    alignmentColor: '#cc2020',
                    moduleScale: 0.92,
                    gradientType: GradientType::None,
                    finderEffect: FinderEffect::None,
                ),
                'frame' => new QrFrameStyle(shape: FrameShape::Star, frameColor: '#ffffff', decorative: true),
            ],
        ];
    }

    /** @return array{style: QrStyle, frame?: QrFrameStyle} */
    public function get(string $name): array
    {
        return $this->presets[$name] ?? throw new \InvalidArgumentException(sprintf('Unknown QR preset "%s".', $name));
    }

    /** @return list<string> */
    public function names(): array
    {
        return array_keys($this->presets);
    }

    public function register(string $name, QrStyle $style, ?QrFrameStyle $frame = null): void
    {
        $this->presets[$name] = null !== $frame ? ['style' => $style, 'frame' => $frame] : ['style' => $style];
    }
}
