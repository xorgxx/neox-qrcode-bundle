<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Controller;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Renderer\PngRenderer;
use Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;
use Xorgxx\NeoxQrCodeBundle\Service\QrStyleValidator;
use Xorgxx\NeoxQrCodeBundle\Service\UserPresetStore;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\Attribute\RateLimiter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/qrcode', name: 'xorgxx_neox_qrcode_api_')]
final class QrCodeApiController extends AbstractController
{
    public function __construct(
        private readonly QrCodeGenerator $generator,
        private readonly QrStyleValidator $validator,
        private readonly QrPresetRegistry $presets,
        private readonly PngRenderer $pngRenderer,
    ) {
    }

    #[Route('/svg', name: 'svg', methods: ['POST'])]
    #[RateLimiter('xorgxx_neox_qrcode_api')]
    public function svg(Request $request): Response
    {
        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = $preset !== null
                ? $this->generator->generatePreset($content, $preset, $ec)
                : $this->generator->generate($content, $style, $ec, $frame);

            return new Response($result->svg, 200, [
                'Content-Type' => 'image/svg+xml; charset=UTF-8',
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/png', name: 'png', methods: ['POST'])]
    #[RateLimiter('xorgxx_neox_qrcode_api')]
    public function png(Request $request): Response
    {
        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = $preset !== null
                ? $this->generator->generatePreset($content, $preset, $ec)
                : $this->generator->generate($content, $style, $ec, $frame);

            return new Response($this->pngRenderer->fromSvg($result->svg, $style->size), 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/matrix', name: 'matrix', methods: ['POST'])]
    #[RateLimiter('xorgxx_neox_qrcode_api')]
    public function matrix(Request $request): JsonResponse
    {
        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = $preset !== null
                ? $this->generator->generatePreset($content, $preset, $ec)
                : $this->generator->generate($content, $style, $ec, $frame);

            return $this->json([
                'content' => $result->content,
                'size' => $result->matrix->size(),
                'matrix' => $result->matrix->cells,
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/validate', name: 'validate', methods: ['POST'])]
    #[RateLimiter('xorgxx_neox_qrcode_api')]
    public function validate(Request $request): JsonResponse
    {
        try {
            [, $style, $ec] = $this->payload($request);
            $report = $this->validator->validate($style, $ec);

            return $this->json([
                'valid' => $report->valid,
                'contrastRatio' => round($report->contrastRatio, 2),
                'errors' => $report->errors,
                'warnings' => $report->warnings,
            ], $report->valid ? 200 : 422);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/presets', name: 'presets', methods: ['GET'])]
    public function presets(): JsonResponse
    {
        return $this->json(['presets' => $this->presets->names()]);
    }

    #[Route('/user-presets', name: 'user_presets_list', methods: ['GET'])]
    public function userPresetsList(UserPresetStore $store): JsonResponse
    {
        return $this->json(['presets' => $store->all()]);
    }

    #[Route('/user-presets', name: 'user_presets_save', methods: ['POST'])]
    public function userPresetsSave(Request $request, UserPresetStore $store): JsonResponse
    {
        $data = $request->toArray();
        $name = (string) ($data['name'] ?? '');
        $config = $data['config'] ?? [];
        try {
            $store->save($name, $config);
            return $this->json(['ok' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 422);
        }
    }

    #[Route('/user-presets/{name}', name: 'user_presets_delete', methods: ['DELETE'])]
    public function userPresetsDelete(string $name, UserPresetStore $store): JsonResponse
    {
        $store->delete($name);
        return $this->json(['ok' => true]);
    }

    /** @return array{string,QrStyle,ErrorCorrection,?string,?QrFrameStyle} */
    private function payload(Request $request): array
    {
        $data = $request->toArray();
        $content = trim((string) ($data['content'] ?? ''));
        $preset = isset($data['preset']) && $data['preset'] !== '' ? (string) $data['preset'] : null;

        $style = new QrStyle(
            size: (int) ($data['size'] ?? 320),
            margin: (int) ($data['margin'] ?? 4),
            moduleShape: ModuleShape::from((string) ($data['moduleShape'] ?? 'square')),
            finderShape: FinderShape::from((string) ($data['finderShape'] ?? 'square')),
            foreground: (string) ($data['foreground'] ?? '#111111'),
            background: (string) ($data['background'] ?? '#ffffff'),
            finderColor: isset($data['finderColor']) ? (string) $data['finderColor'] : null,
            moduleScale: (float) ($data['moduleScale'] ?? 0.92),
            gradientType: GradientType::from((string) ($data['gradientType'] ?? 'none')),
            gradientTo: isset($data['gradientTo']) ? (string) $data['gradientTo'] : null,
            logoHref: isset($data['logoHref']) ? (string) $data['logoHref'] : null,
            logoScale: (float) ($data['logoScale'] ?? 0.20),
            logoBackground: (bool) ($data['logoBackground'] ?? true),
            alignmentShape: AlignmentShape::from((string) ($data['alignmentShape'] ?? 'square')),
            alignmentColor: isset($data['alignmentColor']) ? (string) $data['alignmentColor'] : null,
            finderIconHref: isset($data['finderIconHref']) ? (string) $data['finderIconHref'] : null,
            finderIconScale: (float) ($data['finderIconScale'] ?? 0.6),
            finderEffect: FinderEffect::from((string) ($data['finderEffect'] ?? 'none')),
            finderGradientTo: isset($data['finderGradientTo']) ? (string) $data['finderGradientTo'] : null,
            finderCenterShape: isset($data['finderCenterShape']) && $data['finderCenterShape'] !== ''
                ? ModuleShape::from((string) $data['finderCenterShape'])
                : null,
            finderEyeShape: isset($data['finderEyeShape']) && $data['finderEyeShape'] !== ''
                ? FinderShape::from((string) $data['finderEyeShape'])
                : null,
        );

        $frameShape = FrameShape::from((string) ($data['frameShape'] ?? 'none'));
        $frame = $frameShape !== FrameShape::None || isset($data['frameLabel'])
            ? new QrFrameStyle(
                shape: $frameShape,
                label: isset($data['frameLabel']) && $data['frameLabel'] !== '' ? (string) $data['frameLabel'] : null,
                labelColor: isset($data['frameLabelColor']) ? (string) $data['frameLabelColor'] : null,
                frameColor: isset($data['frameColor']) ? (string) $data['frameColor'] : null,
                decorative: (bool) ($data['frameDecorative'] ?? true),
                decorativeOpacity: isset($data['frameDecorativeOpacity']) ? (float) $data['frameDecorativeOpacity'] : 0.6,
            )
            : null;

        return [$content, $style, ErrorCorrection::from((string) ($data['errorCorrection'] ?? 'H')), $preset, $frame];
    }
}
