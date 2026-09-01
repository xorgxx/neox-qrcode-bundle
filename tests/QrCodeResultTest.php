<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Model\QrCodeResult;
use Xorgxx\NeoxQrCodeBundle\Model\QrMatrix;

final class QrCodeResultTest extends TestCase
{
    public function testDataUri(): void
    {
        $matrix = new QrMatrix([[true, false], [false, true]]);
        $result = new QrCodeResult(svg: '<svg></svg>', matrix: $matrix, content: 'test');

        self::assertStringStartsWith('data:image/svg+xml;base64,', $result->dataUri());
        self::assertSame(base64_decode(substr($result->dataUri(), strlen('data:image/svg+xml;base64,'))), '<svg></svg>');
    }

    public function testPropertiesAreAccessible(): void
    {
        $matrix = new QrMatrix([[true]]);
        $result = new QrCodeResult(svg: '<svg/>', matrix: $matrix, content: 'hello');

        self::assertSame('<svg/>', $result->svg);
        self::assertSame($matrix, $result->matrix);
        self::assertSame('hello', $result->content);
    }
}
