<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;

final class QrFrameStyleTest extends TestCase
{
    public function testDefaultsToNoneShape(): void
    {
        $frame = new QrFrameStyle();

        self::assertSame(FrameShape::None, $frame->shape);
        self::assertNull($frame->label);
    }

    public function testAcceptsLabelAndColor(): void
    {
        $frame = new QrFrameStyle(shape: FrameShape::Circle, label: 'Scan me', labelColor: '#112233');

        self::assertSame('Scan me', $frame->label);
        self::assertSame('#112233', $frame->labelColor);
    }

    public function testInvalidLabelColorThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrFrameStyle(labelColor: 'blue');
    }

    public function testLabelTooLongThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrFrameStyle(label: str_repeat('a', 61));
    }
}
