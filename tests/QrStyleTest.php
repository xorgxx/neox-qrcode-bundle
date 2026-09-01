<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use PHPUnit\Framework\TestCase;

final class QrStyleTest extends TestCase
{
    public function testStyleAcceptsHeartModules(): void
    {
        $style = new QrStyle(moduleShape: ModuleShape::Heart);
        self::assertSame(ModuleShape::Heart, $style->moduleShape);
    }

    public function testInvalidColorIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrStyle(foreground: 'red');
    }

    public function testFinderIconScaleOutOfRangeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrStyle(finderIconScale: 0.1);
    }

    public function testFinderIconHrefUnsafeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrStyle(finderIconHref: 'https://external.example.com/icon.png');
    }

    public function testFinderIconHrefRelativeIsAccepted(): void
    {
        $style = new QrStyle(finderIconHref: '/images/icon.svg');
        self::assertSame('/images/icon.svg', $style->finderIconHref);
    }

    public function testFinderGradientColorInvalidThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrStyle(finderEffect: FinderEffect::Gradient, finderGradientTo: 'not-a-color');
    }
}
