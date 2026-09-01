<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;

final class QrPresetRegistryTest extends TestCase
{
    public function testKnownPresetsExist(): void
    {
        $registry = new QrPresetRegistry();
        $names = $registry->names();

        self::assertContains('classic', $names);
        self::assertContains('dots', $names);
        self::assertContains('rounded', $names);
        self::assertContains('heart', $names);
        self::assertContains('gold', $names);
        self::assertContains('neon', $names);
        self::assertContains('liquid-security', $names);
        self::assertContains('liquid-heart', $names);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('newPresetProvider')]
    public function testNewPresetsGenerateValidSvg(string $preset): void
    {
        $generator = new \Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator(
            new \Xorgxx\NeoxQrCodeBundle\Service\QrMatrixGenerator(),
            $svgRenderer = new \Xorgxx\NeoxQrCodeBundle\Renderer\SvgRenderer(new \Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry()),
            new \Xorgxx\NeoxQrCodeBundle\Service\QrStyleValidator(),
            new QrPresetRegistry(),
            new \Xorgxx\NeoxQrCodeBundle\Renderer\FrameRenderer($svgRenderer),
        );

        $result = $generator->generatePreset('https://example.com', $preset);

        self::assertStringStartsWith('<svg', $result->svg);
    }

    /** @return array<int, array{string}> */
    public static function newPresetProvider(): array
    {
        return [
            ['neon'],
            ['liquid-security'],
            ['liquid-heart'],
        ];
    }

    public function testGetReturnsStyle(): void
    {
        $registry = new QrPresetRegistry();

        $config = $registry->get('classic');
        self::assertArrayHasKey('style', $config);
        self::assertInstanceOf(QrStyle::class, $config['style']);
    }

    public function testLiquidSecurityHasFrame(): void
    {
        $registry = new QrPresetRegistry();

        $config = $registry->get('liquid-security');
        $frame = $config['frame'] ?? null;
        self::assertNotNull($frame);
        self::assertSame(\Xorgxx\NeoxQrCodeBundle\Enum\FrameShape::Security, $frame->shape);
    }

    public function testLiquidHeartHasFrame(): void
    {
        $registry = new QrPresetRegistry();

        $config = $registry->get('liquid-heart');
        $frame = $config['frame'] ?? null;
        self::assertNotNull($frame);
        self::assertSame(\Xorgxx\NeoxQrCodeBundle\Enum\FrameShape::Heart, $frame->shape);
        self::assertTrue($frame->decorative);
    }

    public function testGetUnknownThrows(): void
    {
        $registry = new QrPresetRegistry();

        $this->expectException(\InvalidArgumentException::class);
        $registry->get('nonexistent');
    }

    public function testRegisterCustomPreset(): void
    {
        $registry = new QrPresetRegistry();
        $style = new QrStyle();
        $registry->register('custom', $style);

        self::assertContains('custom', $registry->names());
        $config = $registry->get('custom');
        self::assertSame($style, $config['style']);
    }
}
