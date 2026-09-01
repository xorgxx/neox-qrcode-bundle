<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use Xorgxx\NeoxQrCodeBundle\Renderer\PngRenderer;
use PHPUnit\Framework\TestCase;

final class PngRendererTest extends TestCase
{
    public function testThrowsWithoutImagick(): void
    {
        if (class_exists(\Imagick::class)) {
            self::markTestSkipped('Imagick is available; skipping fallback test.');
        }

        $renderer = new PngRenderer();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Imagick');
        $renderer->fromSvg('<svg></svg>', 320);
    }
}
