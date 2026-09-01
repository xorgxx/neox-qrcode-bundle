<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;
use Xorgxx\NeoxQrCodeBundle\Service\UserPresetStore;

final class QrCodeStudioController extends AbstractController
{
    private const DEFAULT_CONTENT = 'https://neox.dev';

    public function __construct(
        private readonly QrPresetRegistry $presets,
        private readonly QrCodeGenerator $generator,
        private readonly UserPresetStore $userPresetStore,
    ) {
    }

    #[Route('/qrcode/studio', name: 'xorgxx_neox_qrcode_studio', methods: ['GET'])]
    public function index(): Response
    {
        $presetPreviews = [];
        $presetConfigs = [];
        foreach ($this->presets->names() as $name) {
            try {
                $presetPreviews[$name] = $this->generator->generatePreset(self::DEFAULT_CONTENT, $name)->svg;
                $config = $this->presets->get($name);
                $style = $config['style'];
                $frame = $config['frame'] ?? null;
                $presetConfigs[$name] = [
                    'moduleShape' => $style->moduleShape->value,
                    'finderShape' => $style->finderShape->value,
                    'foreground' => $style->foreground,
                    'background' => $style->background,
                    'finderColor' => $style->finderColor,
                    'moduleScale' => $style->moduleScale,
                    'gradientType' => $style->gradientType->value,
                    'gradientTo' => $style->gradientTo,
                    'logoHref' => $style->logoHref,
                    'logoScale' => $style->logoScale,
                    'logoBackground' => $style->logoBackground,
                    'alignmentShape' => $style->alignmentShape->value,
                    'alignmentColor' => $style->alignmentColor,
                    'finderIconHref' => $style->finderIconHref,
                    'finderIconScale' => $style->finderIconScale,
                    'finderEffect' => $style->finderEffect->value,
                    'finderGradientTo' => $style->finderGradientTo,
                    'finderEyeShape' => $style->finderEyeShape?->value,
                    'frameShape' => $frame?->shape->value ?? 'none',
                    'frameLabel' => $frame?->label,
                    'frameLabelColor' => $frame?->labelColor,
                    'frameColor' => $frame?->frameColor,
                    'frameDecorative' => null !== $frame ? $frame->decorative : true,
                    'frameDecorativeOpacity' => null !== $frame ? $frame->decorativeOpacity : 0.6,
                ];
            } catch (\Throwable) {
                continue;
            }
        }

        // Matches the default values of the HTML form fields exactly, so the
        // page never displays an empty preview while the client-side script
        // loads (or if it is blocked, e.g. by a strict CSP without a nonce).
        $initialSvg = $this->generator->generate(self::DEFAULT_CONTENT, new QrStyle(size: 360, margin: 4))->svg;

        $userPresets = [];
        foreach ($this->userPresetStore->all() as $name => $entry) {
            $userPresets[$name] = $entry['config'];
        }

        return $this->render('@NeoxQrCode/studio.html.twig', [
            'initialContent' => self::DEFAULT_CONTENT,
            'initialSvg' => $initialSvg,
            'presetPreviews' => $presetPreviews,
            'presetConfigs' => $presetConfigs,
            'userPresets' => $userPresets,
            'svgEndpoint' => $this->generateUrl('xorgxx_neox_qrcode_api_svg'),
            'pngEndpoint' => $this->generateUrl('xorgxx_neox_qrcode_api_png'),
            'validateEndpoint' => $this->generateUrl('xorgxx_neox_qrcode_api_validate'),
            'userPresetsEndpoint' => $this->generateUrl('xorgxx_neox_qrcode_api_user_presets_list'),
        ]);
    }
}
