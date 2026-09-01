<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
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
}
