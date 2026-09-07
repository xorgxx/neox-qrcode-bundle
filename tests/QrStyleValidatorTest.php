<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEyeShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\QrStyleValidator;

final class QrStyleValidatorTest extends TestCase
{
    private QrStyleValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new QrStyleValidator();
    }

    public function testValidStylePasses(): void
    {
        $report = $this->validator->validate(new QrStyle());

        self::assertTrue($report->valid);
        self::assertSame([], $report->errors);
    }

    public function testLowContrastFails(): void
    {
        $style = new QrStyle(foreground: '#ffffff', background: '#ffffff');

        $report = $this->validator->validate($style);

        self::assertFalse($report->valid);
        self::assertNotEmpty($report->errors);
    }

    public function testLowContrastWarning(): void
    {
        $style = new QrStyle(foreground: '#888888', background: '#ffffff');

        $report = $this->validator->validate($style);

        self::assertTrue($report->valid);
        self::assertNotEmpty($report->warnings);
    }

    public function testSmallMarginWarning(): void
    {
        $style = new QrStyle(margin: 2);

        $report = $this->validator->validate($style);

        self::assertNotEmpty($report->warnings);
    }

    public function testSmallModuleScaleWarning(): void
    {
        $style = new QrStyle(moduleScale: 0.50);

        $report = $this->validator->validate($style);

        self::assertNotEmpty($report->warnings);
    }

    public function testLogoWithoutHighErrorCorrectionWarning(): void
    {
        $style = new QrStyle(logoHref: '/logo.svg');

        $report = $this->validator->validate($style, ErrorCorrection::Medium);

        self::assertNotEmpty($report->warnings);
    }

    public function testLargeLogoWarning(): void
    {
        $style = new QrStyle(logoHref: '/logo.svg', logoScale: 0.28);

        $report = $this->validator->validate($style);

        self::assertNotEmpty($report->warnings);
    }

    public function testContrastRatioIsCalculated(): void
    {
        $report = $this->validator->validate(new QrStyle());

        self::assertGreaterThan(0.0, $report->contrastRatio);
    }

    public function testDefaultStyleHasHighEstimatedReadability(): void
    {
        $report = $this->validator->validate(new QrStyle());

        self::assertGreaterThanOrEqual(90, $report->readabilityScore);
        self::assertLessThanOrEqual(100, $report->readabilityScore);
        self::assertSame(4, $report->readabilityDetails['margin']);
        self::assertSame('H', $report->readabilityDetails['errorCorrection']);
    }

    public function testInvalidStyleScoreIsCappedBelowFifty(): void
    {
        $report = $this->validator->validate(new QrStyle(foreground: '#ffffff', background: '#ffffff'));

        self::assertFalse($report->valid);
        self::assertLessThanOrEqual(49, $report->readabilityScore);
    }

    public function testRiskyDecorationsReduceEstimatedReadability(): void
    {
        $safe = $this->validator->validate(new QrStyle());
        $risky = $this->validator->validate(new QrStyle(
            margin: 1,
            moduleShape: ModuleShape::Heart,
            moduleScale: 0.50,
            logoHref: '/logo.svg',
            logoScale: 0.28,
            finderIconHref: '/finder.svg',
            finderEyeShape: FinderEyeShape::Star,
        ), ErrorCorrection::Medium);

        self::assertLessThan($safe->readabilityScore, $risky->readabilityScore);
    }

    public function testLiquidModulesHaveAReadabilityPenaltyAndWarning(): void
    {
        $square = $this->validator->validate(new QrStyle(moduleShape: ModuleShape::Square));
        $liquid = $this->validator->validate(new QrStyle(moduleShape: ModuleShape::Liquid));

        self::assertSame($square->readabilityScore - 5, $liquid->readabilityScore);
        self::assertSame('liquid', $liquid->readabilityDetails['moduleShape']);
        self::assertNotEmpty(array_filter(
            $liquid->warnings,
            static fn (string $warning): bool => str_contains($warning, 'Liquid modules'),
        ));
    }

    public function testConnectedShapesHaveDocumentedReadabilityPenalties(): void
    {
        $square = $this->validator->validate(new QrStyle(moduleShape: ModuleShape::Square));

        foreach ([
            ModuleShape::Blob->value => 4,
            ModuleShape::Wave->value => 7,
            ModuleShape::Cross->value => 8,
        ] as $shape => $penalty) {
            $report = $this->validator->validate(new QrStyle(moduleShape: ModuleShape::from($shape)));

            self::assertSame($square->readabilityScore - $penalty, $report->readabilityScore);
            self::assertNotEmpty($report->warnings);
        }
    }

    public function testDecorativeFrameReducesEstimatedReadability(): void
    {
        $style = new QrStyle();
        $withoutFrame = $this->validator->validate($style);
        $withFrame = $this->validator->validate($style, ErrorCorrection::High, new QrFrameStyle(shape: FrameShape::Star));

        self::assertLessThan($withoutFrame->readabilityScore, $withFrame->readabilityScore);
    }
}
