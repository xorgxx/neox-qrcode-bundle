<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Model\QrCodeResult;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Renderer\FrameRenderer;
use Xorgxx\NeoxQrCodeBundle\Renderer\SvgRenderer;

final readonly class QrCodeGenerator
{
    public function __construct(
        private QrMatrixGenerator $matrixGenerator,
        private SvgRenderer $renderer,
        private QrStyleValidator $validator,
        private QrPresetRegistry $presets,
        private FrameRenderer $frameRenderer,
    ) {
    }

    public function generate(
        string $content,
        ?QrStyle $style = null,
        ErrorCorrection $errorCorrection = ErrorCorrection::High,
        ?QrFrameStyle $frame = null,
    ): QrCodeResult {
        $style ??= new QrStyle();
        $report = $this->validator->validate($style, $errorCorrection);
        if (!$report->valid) {
            throw new \InvalidArgumentException(implode(' ', $report->errors));
        }

        $matrix = $this->matrixGenerator->generate($content, $errorCorrection);
        $svg = $frame !== null
            ? $this->frameRenderer->render($matrix, $style, $frame)
            : $this->renderer->render($matrix, $style);

        return new QrCodeResult(
            svg: $svg,
            matrix: $matrix,
            content: $content,
        );
    }

    public function generatePreset(
        string $content,
        string $preset,
        ErrorCorrection $errorCorrection = ErrorCorrection::High,
    ): QrCodeResult {
        $config = $this->presets->get($preset);
        return $this->generate($content, $config['style'], $errorCorrection, $config['frame'] ?? null);
    }
}
