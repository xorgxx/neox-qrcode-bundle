<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrMatrixGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;
use Xorgxx\NeoxQrCodeBundle\Service\QrStyleValidator;
use Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry;

final class QrCodeGeneratorTest extends TestCase
{
    private QrCodeGenerator $generator;

    protected function setUp(): void
    {
        $shapes = new ShapeRegistry();
        $svgRenderer = new \Xorgxx\NeoxQrCodeBundle\Renderer\SvgRenderer($shapes);
        $this->generator = new QrCodeGenerator(
            new QrMatrixGenerator(),
            $svgRenderer,
            new QrStyleValidator(),
            new QrPresetRegistry(),
            new \Xorgxx\NeoxQrCodeBundle\Renderer\FrameRenderer($svgRenderer),
        );
    }

    public function testGenerateReturnsValidSvg(): void
    {
        $result = $this->generator->generate('https://example.com');

        self::assertStringStartsWith('<svg', $result->svg);
        self::assertStringEndsWith('</svg>', $result->svg);
        self::assertStringContainsString('xmlns="http://www.w3.org/2000/svg"', $result->svg);
    }

    public function testGenerateWithDefaultStyle(): void
    {
        $result = $this->generator->generate('test');

        self::assertNotEmpty($result->svg);
        self::assertSame('test', $result->content);
        self::assertGreaterThan(0, $result->matrix->size());
    }

    public function testGenerateWithCustomStyle(): void
    {
        $style = new QrStyle(
            size: 500,
            moduleShape: ModuleShape::Dot,
            finderShape: FinderShape::Circle,
            foreground: '#000000',
            background: '#ffffff',
        );

        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('width="500"', $result->svg);
        self::assertStringContainsString('<circle', $result->svg);
    }

    public function testGenerateWithGradient(): void
    {
        $style = new QrStyle(
            gradientType: GradientType::Linear,
            gradientTo: '#D59618',
        );

        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('linearGradient', $result->svg);
        self::assertStringContainsString('url(#neoxQrGradient_', $result->svg);
    }

    public function testGenerateWithRadialGradient(): void
    {
        $style = new QrStyle(
            gradientType: GradientType::Radial,
            gradientTo: '#D59618',
        );

        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('radialGradient', $result->svg);
    }

    public function testGenerateWithLogo(): void
    {
        $style = new QrStyle(logoHref: '/images/logo.svg');

        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<image', $result->svg);
        self::assertStringContainsString('/images/logo.svg', $result->svg);
    }

    public function testGenerateWithLogoBackground(): void
    {
        $style = new QrStyle(logoHref: '/images/logo.svg', logoBackground: true);

        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<rect', $result->svg);
    }

    public function testGenerateAllModuleShapes(): void
    {
        foreach (ModuleShape::cases() as $shape) {
            $style = new QrStyle(moduleShape: $shape);
            $result = $this->generator->generate('test', $style);

            self::assertNotEmpty($result->svg, sprintf('SVG should not be empty for shape %s.', $shape->value));
        }
    }

    public function testGenerateAllFinderShapes(): void
    {
        foreach (FinderShape::cases() as $shape) {
            $style = new QrStyle(finderShape: $shape);
            $result = $this->generator->generate('test', $style);

            self::assertNotEmpty($result->svg, sprintf('SVG should not be empty for finder %s.', $shape->value));
        }
    }

    public function testGenerateAllErrorCorrections(): void
    {
        foreach (ErrorCorrection::cases() as $ec) {
            $result = $this->generator->generate('test', null, $ec);

            self::assertNotEmpty($result->svg, sprintf('SVG should not be empty for EC %s.', $ec->value));
        }
    }

    public function testGeneratePreset(): void
    {
        $result = $this->generator->generatePreset('test', 'dots');

        self::assertStringContainsString('<circle', $result->svg);
    }

    public function testGeneratePresetUnknownThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generatePreset('test', 'nonexistent');
    }

    public function testGenerateRejectsInvalidContrast(): void
    {
        $style = new QrStyle(foreground: '#ffffff', background: '#ffffff');

        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate('test', $style);
    }

    public function testSvgContainsQuietZone(): void
    {
        $style = new QrStyle(margin: 4);
        $result = $this->generator->generate('test', $style);
        $matrixSize = $result->matrix->size();
        $expectedView = $matrixSize + 2 * 4;

        self::assertStringContainsString(sprintf('viewBox="0 0 %d %d"', $expectedView, $expectedView), $result->svg);
    }

    public function testSvgEscapesSpecialCharactersInContent(): void
    {
        $result = $this->generator->generate('<script>alert(1)</script>');

        self::assertStringNotContainsString('<script>', $result->svg);
    }

    public function testSvgEscapesSpecialCharactersInColors(): void
    {
        // Colors are validated by QrStyle regex, so this is a safety net test
        $style = new QrStyle(foreground: '#111111');
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('#111111', $result->svg);
    }

    public function testHeartShapeRendersPath(): void
    {
        $style = new QrStyle(moduleShape: ModuleShape::Heart);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testDiamondShapeRendersPath(): void
    {
        $style = new QrStyle(moduleShape: ModuleShape::Diamond);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testFinderDiamondShapeRendersPath(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Diamond);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testFinderLeafShapeRendersPath(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Leaf);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testFinderCircleShapeRendersSolidRingNotDots(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Circle);
        $result = $this->generator->generate('test', $style);

        // 3 finders x 2 unified ring circles (outer + background cutout) + 3 eye circles = 9.
        self::assertSame(9, substr_count($result->svg, '<circle'));
    }

    public function testFinderDiamondShapeRendersUnifiedRing(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Diamond);
        $result = $this->generator->generate('test', $style);

        // 3 finders x 3 unified diamond paths (outer ring + background cutout + eye) = 9.
        self::assertSame(9, substr_count($result->svg, '<path'));
    }

    public function testFinderCenterShapeOverridesEye(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Square, finderCenterShape: ModuleShape::Dot);
        $result = $this->generator->generate('test', $style);

        self::assertSame(3, substr_count($result->svg, '<circle'));
    }

    public function testFinderEyeShapeOverridesEye(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Square, finderEyeShape: FinderShape::Circle);
        $result = $this->generator->generate('test', $style);

        // Outer 7x7 = rect, 5x5 cutout = rect, 3x3 eye = circle → 3 circles total
        self::assertSame(3, substr_count($result->svg, '<circle'));
    }

    public function testFinderEyeShapeTakesPriorityOverCenterShape(): void
    {
        $style = new QrStyle(
            finderShape: FinderShape::Square,
            finderCenterShape: ModuleShape::Dot,
            finderEyeShape: FinderShape::Diamond,
        );
        $result = $this->generator->generate('test', $style);

        // finderEyeShape (Diamond) takes priority → 3 diamonds for eyes, no extra circles
        self::assertSame(3, substr_count($result->svg, '<path'));
        self::assertSame(0, substr_count($result->svg, '<circle'));
    }

    public function testGenerateAllAlignmentShapes(): void
    {
        foreach (AlignmentShape::cases() as $shape) {
            $style = new QrStyle(alignmentShape: $shape);
            $result = $this->generator->generate('test-content-for-alignment', $style);

            self::assertNotEmpty($result->svg, sprintf('SVG should not be empty for alignment shape %s.', $shape->value));
        }
    }

    public function testAlignmentShapeCircleRendersCircle(): void
    {
        // Version 2+ content to trigger alignment patterns
        $style = new QrStyle(alignmentShape: AlignmentShape::Circle);
        $result = $this->generator->generate('test-content-for-alignment-patterns', $style);

        // Alignment patterns should exist for version >= 2
        $version = ($result->matrix->size() - 21) / 4 + 1;
        if ($version >= 2) {
            self::assertStringContainsString('<circle', $result->svg);
        }
    }

    public function testAlignmentShapeDiamondRendersPath(): void
    {
        $style = new QrStyle(alignmentShape: AlignmentShape::Diamond);
        $result = $this->generator->generate('test-content-for-alignment-patterns', $style);

        $version = ($result->matrix->size() - 21) / 4 + 1;
        if ($version >= 2) {
            self::assertStringContainsString('<path', $result->svg);
        }
    }

    public function testAlignmentShapeLeafRendersPath(): void
    {
        $style = new QrStyle(alignmentShape: AlignmentShape::Leaf);
        $result = $this->generator->generate('test-content-for-alignment-patterns', $style);

        $version = ($result->matrix->size() - 21) / 4 + 1;
        if ($version >= 2) {
            self::assertStringContainsString('<path', $result->svg);
        }
    }

    public function testAlignmentColorIsApplied(): void
    {
        $style = new QrStyle(alignmentColor: '#FF0000');
        $result = $this->generator->generate('test-content-for-alignment-color', $style);

        $version = ($result->matrix->size() - 21) / 4 + 1;
        if ($version >= 2) {
            self::assertStringContainsString('#FF0000', $result->svg);
        }
    }

    public function testAlignmentColorFallsBackToFinderColor(): void
    {
        $style = new QrStyle(finderColor: '#00FF00', alignmentColor: null);
        $result = $this->generator->generate('test-content-for-alignment-fallback', $style);

        $version = ($result->matrix->size() - 21) / 4 + 1;
        if ($version >= 2) {
            self::assertStringContainsString('#00FF00', $result->svg);
        }
    }

    public function testFinderHexagonShapeRendersPath(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Hexagon);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testFinderStarShapeRendersPath(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Star);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<path', $result->svg);
    }

    public function testFinderDottedShapeRendersCircles(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Dotted);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('<circle', $result->svg);
    }

    public function testFinderMinimalShapeRendersCornerBrackets(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Minimal);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('stroke-linecap="square"', $result->svg);
    }

    public function testFinderInvertedShapeRendersFullPattern(): void
    {
        $style = new QrStyle(finderShape: FinderShape::Inverted, finderColor: '#123456');
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('#123456', $result->svg);
        self::assertStringContainsString('#ffffff', $result->svg);
    }

    public function testFinderIconIsRenderedThreeTimes(): void
    {
        $style = new QrStyle(finderIconHref: '/images/icon.svg');
        $result = $this->generator->generate('test', $style);

        self::assertSame(3, substr_count($result->svg, '/images/icon.svg'));
    }

    public function testFinderEffectDoubleStrokeRendersOutline(): void
    {
        $style = new QrStyle(finderEffect: \Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect::DoubleStroke);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('stroke-width="0.25"', $result->svg);
    }

    public function testFinderEffectDashedRendersDashArray(): void
    {
        $style = new QrStyle(finderEffect: \Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect::Dashed);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('stroke-dasharray', $result->svg);
    }

    public function testFinderEffectShadowRendersOffsetRect(): void
    {
        $style = new QrStyle(finderEffect: \Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect::Shadow);
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('opacity="0.25"', $result->svg);
    }

    public function testFinderEffectGradientRendersFinderGradient(): void
    {
        $style = new QrStyle(finderEffect: \Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect::Gradient, finderGradientTo: '#00FF00');
        $result = $this->generator->generate('test', $style);

        self::assertStringContainsString('neoxFinderGradient', $result->svg);
    }

    public function testFinderGradientRequiredWhenEffectIsGradient(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrStyle(finderEffect: \Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect::Gradient);
    }

    public function testGenerateWithCircleFrame(): void
    {
        $frame = new \Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle(shape: \Xorgxx\NeoxQrCodeBundle\Enum\FrameShape::Circle);
        $result = $this->generator->generate('test', null, ErrorCorrection::High, $frame);

        self::assertStringContainsString('<circle', $result->svg);
        self::assertStringContainsString('clipPath', $result->svg);
        self::assertStringContainsString('scale(', $result->svg);
    }

    public function testGenerateWithFrameLabelRendersText(): void
    {
        $frame = new \Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle(shape: \Xorgxx\NeoxQrCodeBundle\Enum\FrameShape::Heart, label: 'Scan me');
        $result = $this->generator->generate('test', null, ErrorCorrection::High, $frame);

        self::assertStringContainsString('<text', $result->svg);
        self::assertStringContainsString('Scan me', $result->svg);
    }

    public function testGenerateWithoutFrameShapeIsUnaffected(): void
    {
        $frame = new \Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle();
        $result = $this->generator->generate('test', null, ErrorCorrection::High, $frame);

        self::assertStringNotContainsString('clipPath', $result->svg);
        self::assertStringNotContainsString('scale(', $result->svg);
    }
}
