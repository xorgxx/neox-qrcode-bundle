<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;

#[AsTwigComponent(name: 'NeoxQrCodeEditor', template: '@NeoxQrCode/components/QrCodeEditor.html.twig')]
final class QrCodeEditor
{
    public const DEFAULT_FOREGROUND = '#111111';
    public const DEFAULT_BACKGROUND = '#ffffff';
    public const DEFAULT_FINDER_COLOR = '#111111';
    public const DEFAULT_ALIGNMENT_COLOR = '#111111';
    public const DEFAULT_GRADIENT_TO = '#D59618';
    public const DEFAULT_SIZE = 360;
    public const DEFAULT_MARGIN = 4;
    public const DEFAULT_MODULE_SCALE = 0.92;

    public string $content = 'https://example.com';
    public string $endpoint = '/api/qrcode/svg';
    public string $downloadEndpoint = '/api/qrcode/png';

    public function __construct(private readonly QrPresetRegistry $presets)
    {
    }

    /** @return list<string> */
    public function getPresets(): array
    {
        return $this->presets->names();
    }

    /** @return array<string,mixed> */
    public function getDefaults(): array
    {
        return [
            'foreground' => self::DEFAULT_FOREGROUND,
            'background' => self::DEFAULT_BACKGROUND,
            'finderColor' => self::DEFAULT_FINDER_COLOR,
            'alignmentColor' => self::DEFAULT_ALIGNMENT_COLOR,
            'gradientTo' => self::DEFAULT_GRADIENT_TO,
            'size' => self::DEFAULT_SIZE,
            'margin' => self::DEFAULT_MARGIN,
            'moduleScale' => self::DEFAULT_MODULE_SCALE,
        ];
    }
}
