<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Component;

use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEyeShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderFrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;
use Xorgxx\NeoxQrCodeBundle\Service\UserPresetStore;

#[AsTwigComponent(name: 'NeoxQrCode', template: '@NeoxQrCode/components/QrCode.html.twig')]
final class QrCode
{
    public string $content = '';
    public int $size = 320;
    public int $margin = 4;
    public string $moduleShape = 'square';
    public string $finderShape = 'square';
    public ?string $finderFrameShape = null;
    public string $foreground = '#111111';
    public string $background = '#ffffff';
    public ?string $finderColor = null;
    public string $alignmentShape = 'square';
    public ?string $alignmentColor = null;
    public string $errorCorrection = 'H';
    public float $moduleScale = 0.92;
    public string $gradientType = 'none';
    public ?string $gradientTo = null;
    public ?string $logoHref = null;
    public float $logoScale = 0.20;
    public bool $logoBackground = true;
    public ?string $finderIconHref = null;
    public float $finderIconScale = 0.6;
    public string $finderEffect = 'none';
    public ?string $finderGradientTo = null;
    public ?string $finderEyeShape = null;
    public float $finderEyeScale = 1.0;
    public string $frameShape = 'none';
    public ?string $frameLabel = null;
    public ?string $frameLabelColor = null;
    public ?string $frameColor = null;
    public ?string $frameHeader = null;
    public bool $frameDecorative = true;
    public float $frameDecorativeOpacity = 0.6;
    public ?string $preset = null;
    public bool $interactive = false;
    public bool $presetSelector = false;
    public bool $bare = false;
    public bool $transparent = false;

    public function __construct(
        private readonly QrCodeGenerator $generator,
        private readonly QrPresetRegistry $presetRegistry,
        private readonly UserPresetStore $userPresetStore,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Route-dependent URLs are resolved lazily and return null when the app
     * did not import the bundle routes, so <twig:NeoxQrCode> still renders
     * a static SVG instead of crashing the page.
     */
    public function getSvgEndpoint(): ?string
    {
        return $this->routeUrl('xorgxx_neox_qrcode_api_svg');
    }

    public function getStudioUrl(): ?string
    {
        return $this->routeUrl('xorgxx_neox_qrcode_studio');
    }

    private function routeUrl(string $route): ?string
    {
        try {
            return $this->urlGenerator->generate($route);
        } catch (RoutingException) {
            return null;
        }
    }

    public function getSvg(): string
    {
        if (null !== $this->preset) {
            try {
                return $this->generator->generatePreset(
                    $this->content,
                    $this->preset,
                    ErrorCorrection::from($this->errorCorrection),
                )->svg;
            } catch (\InvalidArgumentException) {
                // Not a built-in preset: it may be a user-saved one.
            }

            $config = $this->userPresetStore->getConfig($this->preset);

            return $this->generator->generate(
                $this->content,
                QrStyle::fromArray($config),
                ErrorCorrection::from((string) ($config['errorCorrection'] ?? $this->errorCorrection)),
                QrFrameStyle::fromArray($config),
            )->svg;
        }

        $style = new QrStyle(
            size: $this->size,
            margin: $this->margin,
            moduleShape: ModuleShape::from($this->moduleShape),
            finderShape: FinderShape::from($this->finderShape),
            foreground: $this->foreground,
            background: $this->transparent ? 'transparent' : $this->background,
            finderColor: $this->finderColor,
            moduleScale: $this->moduleScale,
            gradientType: GradientType::from($this->gradientType),
            gradientTo: $this->gradientTo,
            logoHref: $this->logoHref,
            logoScale: $this->logoScale,
            logoBackground: $this->logoBackground,
            finderIconHref: $this->finderIconHref,
            finderIconScale: $this->finderIconScale,
            finderEffect: FinderEffect::from($this->finderEffect),
            finderGradientTo: $this->finderGradientTo,
            alignmentShape: AlignmentShape::from($this->alignmentShape),
            alignmentColor: $this->alignmentColor,
            finderEyeShape: null !== $this->finderEyeShape && '' !== $this->finderEyeShape ? FinderEyeShape::from($this->finderEyeShape) : null,
            finderFrameShape: null !== $this->finderFrameShape && '' !== $this->finderFrameShape ? FinderFrameShape::from($this->finderFrameShape) : null,
            finderEyeScale: $this->finderEyeScale,
        );

        $frameShape = FrameShape::from($this->frameShape);
        $frame = FrameShape::None !== $frameShape || null !== $this->frameLabel
            ? new QrFrameStyle($frameShape, $this->frameLabel, $this->frameLabelColor, $this->frameColor, $this->frameHeader, $this->frameDecorative, $this->frameDecorativeOpacity)
            : null;

        return $this->generator->generate(
            $this->content,
            $style,
            ErrorCorrection::from($this->errorCorrection),
            $frame,
        )->svg;
    }

    /** @return list<string> */
    public function getBuiltinPresets(): array
    {
        return $this->presetRegistry->names();
    }

    /** @return array<string, array<string, mixed>> */
    public function getUserPresets(): array
    {
        return $this->userPresetStore->all();
    }
}
