<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Xorgxx\NeoxQrCodeBundle\Decoder\QrDecoderInterface;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\ReadabilityProfile;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Renderer\PngRenderer;
use Xorgxx\NeoxQrCodeBundle\Service\QrCodeGenerator;
use Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry;
use Xorgxx\NeoxQrCodeBundle\Service\QrStyleValidator;
use Xorgxx\NeoxQrCodeBundle\Service\UserPresetStore;

#[Route('/api/qrcode', name: 'xorgxx_neox_qrcode_api_')]
final class QrCodeApiController extends AbstractController
{
    public function __construct(
        private readonly QrCodeGenerator $generator,
        private readonly QrStyleValidator $validator,
        private readonly QrPresetRegistry $presets,
        private readonly PngRenderer $pngRenderer,
        private readonly QrDecoderInterface $decoder,
        private readonly ?RateLimiterFactory $apiRateLimiter = null,
    ) {
    }

    #[Route('/svg', name: 'svg', methods: ['POST'])]
    public function svg(Request $request): Response
    {
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = null !== $preset
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
    public function png(Request $request): Response
    {
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = null !== $preset
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
    public function matrix(Request $request): JsonResponse
    {
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

        try {
            [$content, $style, $ec, $preset, $frame] = $this->payload($request);
            $result = null !== $preset
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
    public function validate(Request $request): JsonResponse
    {
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

        try {
            [$content, $style, $ec, $preset, $frame, $profile] = $this->payload($request);
            if (null !== $preset) {
                $config = $this->presets->get($preset);
                $style = $config['style'];
                $frame = $config['frame'] ?? null;
            }
            $report = $this->validator->validate($style, $ec, $frame, $profile);
            $decode = null;
            if ($report->valid && '' !== $content) {
                $result = null !== $preset
                    ? $this->generator->generatePreset($content, $preset, $ec)
                    : $this->generator->generate($content, $style, $ec, $frame);
                $decode = $this->decoder->decode($result->svg, $content, $profile);
            }

            return $this->json([
                'valid' => $report->valid,
                'contrastRatio' => null !== $report->contrastRatio ? round($report->contrastRatio, 2) : null,
                'readabilityScore' => $report->readabilityScore,
                'readabilityDetails' => $report->readabilityDetails,
                'estimated' => true,
                'testProfile' => $profile->value,
                'testSizes' => $profile->sizes(),
                'serverDecode' => null === $decode ? null : [
                    'available' => $decode->available,
                    'successfulSizes' => $decode->successfulSizes,
                    'decodedContentMatches' => null !== $decode->decodedContent ? hash_equals($content, $decode->decodedContent) : null,
                    'message' => $decode->message,
                ],
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
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

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
    public function userPresetsDelete(string $name, UserPresetStore $store, Request $request): JsonResponse
    {
        if (!$this->isAcceptedByRateLimiter($request)) {
            return $this->tooManyRequests();
        }

        $store->delete($name);

        return $this->json(['ok' => true]);
    }

    /**
     * The limiter is nullable so the bundle works without symfony/rate-limiter
     * or when `xorgxx_neox_qrcode_api` is not configured; importing
     * `config/rate_limiter.yaml` activates it transparently.
     */
    private function isAcceptedByRateLimiter(Request $request): bool
    {
        if (null === $this->apiRateLimiter) {
            return true;
        }

        return $this->apiRateLimiter
            ->create($request->getClientIp() ?? 'unknown')
            ->consume(1)
            ->isAccepted();
    }

    private function tooManyRequests(): JsonResponse
    {
        return new JsonResponse(['error' => 'Too many requests.'], Response::HTTP_TOO_MANY_REQUESTS);
    }

    /** @return array{string,QrStyle,ErrorCorrection,?string,?QrFrameStyle,ReadabilityProfile} */
    private function payload(Request $request): array
    {
        $data = $request->toArray();
        $content = trim((string) ($data['content'] ?? ''));
        $preset = isset($data['preset']) && '' !== $data['preset'] ? (string) $data['preset'] : null;
        $style = QrStyle::fromArray($data);
        $frame = QrFrameStyle::fromArray($data);

        return [
            $content,
            $style,
            ErrorCorrection::from((string) ($data['errorCorrection'] ?? 'H')),
            $preset,
            $frame,
            ReadabilityProfile::from((string) ($data['testProfile'] ?? 'balanced')),
        ];
    }
}
