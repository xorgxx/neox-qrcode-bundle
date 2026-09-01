<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Service\QrMatrixGenerator;

final class QrMatrixGeneratorTest extends TestCase
{
    private QrMatrixGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new QrMatrixGenerator();
    }

    public function testMatrixIsNonEmpty(): void
    {
        $matrix = $this->generator->generate('https://example.com');

        self::assertGreaterThan(0, $matrix->size());
        self::assertNotEmpty($matrix->cells);
    }

    public function testMatrixIsSquare(): void
    {
        $matrix = $this->generator->generate('test');

        self::assertSame(count($matrix->cells), count($matrix->cells[0]));
    }

    public function testDeterminismForSameInput(): void
    {
        $a = $this->generator->generate('hello', ErrorCorrection::High);
        $b = $this->generator->generate('hello', ErrorCorrection::High);

        self::assertSame($a->cells, $b->cells);
    }

    public function testDifferentErrorCorrectionProducesDifferentMatrix(): void
    {
        $low = $this->generator->generate('hello', ErrorCorrection::Low);
        $high = $this->generator->generate('hello', ErrorCorrection::High);

        self::assertNotSame($low->cells, $high->cells);
    }

    public function testEmptyContentIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate('   ');
    }

    public function testContentTooLongIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->generator->generate(str_repeat('x', 4097));
    }

    public function testIsDarkReturnsFalseForOutOfBounds(): void
    {
        $matrix = $this->generator->generate('test');

        self::assertFalse($matrix->isDark(9999, 9999));
    }

    public function testMatrixContainsDarkModules(): void
    {
        $matrix = $this->generator->generate('test');
        $hasDark = false;

        for ($y = 0; $y < $matrix->size(); ++$y) {
            for ($x = 0; $x < $matrix->size(); ++$x) {
                if ($matrix->isDark($x, $y)) {
                    $hasDark = true;
                    break 2;
                }
            }
        }

        self::assertTrue($hasDark, 'Matrix should contain at least one dark module.');
    }
}
