<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use Xorgxx\NeoxQrCodeBundle\Model\QrMatrix;
use PHPUnit\Framework\TestCase;

final class QrMatrixTest extends TestCase
{
    public function testValidSquareMatrix(): void
    {
        $matrix = new QrMatrix([[true, false], [false, true]]);

        self::assertSame(2, $matrix->size());
        self::assertTrue($matrix->isDark(0, 0));
        self::assertFalse($matrix->isDark(1, 0));
    }

    public function testEmptyMatrixThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrMatrix([]);
    }

    public function testNonSquareMatrixThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QrMatrix([[true, false], [true]]);
    }

    public function testIsDarkOutOfBoundsReturnsFalse(): void
    {
        $matrix = new QrMatrix([[true]]);

        self::assertFalse($matrix->isDark(5, 5));
        self::assertFalse($matrix->isDark(-1, 0));
    }
}
