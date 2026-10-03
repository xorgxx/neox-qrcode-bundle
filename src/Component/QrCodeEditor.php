<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Component;

use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    public const DEFAULT_FINDER_EYE_SCALE = 1.0;

    public string $content = 'https://example.com';
    public ?string $endpoint = null;
    public ?string $downloadEndpoint = null;
    public ?string $validateEndpoint = null;

    public function __construct(
        private readonly QrPresetRegistry $presets,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Endpoints resolve from the real route names so the editor keeps working
     * when routes are mounted under a prefix; the literal paths remain the
     * fallback when routes are not imported.
     */
    public function getEndpoint(): string
    {
        return $this->endpoint ?? $this->routeUrl('xorgxx_neox_qrcode_api_svg', '/api/qrcode/svg');
    }

    public function getDownloadEndpoint(): string
    {
        return $this->downloadEndpoint ?? $this->routeUrl('xorgxx_neox_qrcode_api_png', '/api/qrcode/png');
    }

    public function getValidateEndpoint(): string
    {
        return $this->validateEndpoint ?? $this->routeUrl('xorgxx_neox_qrcode_api_validate', '/api/qrcode/validate');
    }

    private function routeUrl(string $route, string $fallback): string
    {
        try {
            return $this->urlGenerator->generate($route);
        } catch (RoutingException) {
            return $fallback;
        }
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
            'finderEyeScale' => self::DEFAULT_FINDER_EYE_SCALE,
        ];
    }
}
