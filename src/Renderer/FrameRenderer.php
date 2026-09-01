<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Renderer;

use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrMatrix;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;

final readonly class FrameRenderer
{
    public function __construct(
        private SvgRenderer $svgRenderer,
    ) {
    }

    public function render(QrMatrix $matrix, QrStyle $style, QrFrameStyle $frame): string
    {
        [$content, $view] = $this->svgRenderer->renderContent($matrix, $style);

        if ($frame->shape === FrameShape::None) {
            return sprintf(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" width="%2$d" height="%2$d" role="img" aria-label="QR Code">%3$s</svg>',
                $view,
                $style->size,
                $content
            );
        }

        $ratio = $this->inscribeRatio($frame->shape);
        $qrArea = $view * $ratio;
        $margin = ($view - $qrArea) / 2;
        $vOffset = $view * $this->verticalOffset($frame->shape);

        $labelHeight = $frame->label !== null ? $view * 0.16 : 0.0;
        $headerHeight = $frame->shape === FrameShape::Security ? $view * 0.15 : 0.0;
        $totalHeight = $view + $labelHeight + $headerHeight;
        $outHeight = (int) round($style->size * $totalHeight / $view);

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %2$.4F" width="%3$d" height="%4$d" role="img" aria-label="QR Code">',
            $view,
            $totalHeight,
            $style->size,
            $outHeight
        );

        $fg = $this->escape($style->foreground);
        $bg = $this->escape($style->background);
        $frameFill = $this->escape($frame->frameColor ?? $style->background);

        // Extract <defs> from content so filters/gradients are defined once at SVG level,
        // avoiding duplicate IDs when content is rendered multiple times.
        $contentDefs = '';
        $contentBody = $content;
        if (preg_match('/(<defs>.*?<\/defs>)(.*)/s', $content, $m)) {
            $contentDefs = $m[1];
            $contentBody = $m[2];
        }

        // Security shape: badge with header band + built-in title
        if ($frame->shape === FrameShape::Security) {
            $svg .= $contentDefs;
            // 1. Badge shape (rounded rect covering full area)
            $svg .= sprintf('<rect width="%d" height="%.4F" rx="%.4F" fill="%s"/>', $view, $totalHeight, $view * 0.08, $frameFill);

            // 2. Header band
            $svg .= sprintf('<rect width="%d" height="%.4F" rx="%.4F" fill="%s"/>', $view, $headerHeight, $view * 0.08, $fg);
            // Clip bottom corners of header (overlay rect to square them off)
            $svg .= sprintf('<rect y="%.4F" width="%d" height="%.4F" fill="%s"/>', $headerHeight * 0.5, $view, $headerHeight * 0.5, $fg);

            // 3. Title text in header
            $svg .= sprintf(
                '<text x="%.4F" y="%.4F" text-anchor="middle" font-family="sans-serif" font-weight="bold" font-size="%.4F" fill="%s" letter-spacing="0.1em">CODE SÉCURITÉ</text>',
                $view / 2,
                $headerHeight * 0.68,
                $headerHeight * 0.42,
                $bg,
            );

            // 4. QR area below header
            $qrScale = $qrArea / $view;
            $qrY = $headerHeight + $margin + $vOffset;
            $svg .= sprintf(
                '<rect x="%.4F" y="%.4F" width="%.4F" height="%.4F" rx="%.4F" fill="%s"/>',
                $margin, $qrY, $qrArea, $qrArea, $qrArea * 0.04, $bg
            );
            $svg .= sprintf(
                '<g transform="translate(%.4F,%.4F) scale(%.6F)">%s</g>',
                $margin, $qrY, $qrScale, $contentBody
            );

            if ($frame->label !== null) {
                $color = $this->escape($frame->labelColor ?? $style->foreground);
                $svg .= sprintf(
                    '<text x="%.4F" y="%.4F" text-anchor="middle" font-family="sans-serif" font-size="%.4F" fill="%s">%s</text>',
                    $view / 2,
                    $totalHeight - $labelHeight * 0.28,
                    $labelHeight * 0.55,
                    $color,
                    $this->escape($frame->label)
                );
            }

            return $svg.'</svg>';
        }

        $clipId = 'neoxFrameClip_' . bin2hex(random_bytes(4));

        // Clip: anything outside the shape is cut
        $svg .= $contentDefs;
        $svg .= sprintf('<defs><clipPath id="%s">%s</clipPath></defs>', $clipId, $this->shapeElement($frame->shape, $view));

        // 1. Shape filled with frame color (defaults to background)
        $svg .= sprintf('<g fill="%s">%s</g>', $frameFill, $this->shapeElement($frame->shape, $view));

        // 2. Decorative QR pattern filling the whole shape (optional, very light opacity)
        if ($frame->decorative) {
            $svg .= sprintf('<g clip-path="url(#%s)" opacity="%.2F">%s</g>', $clipId, $frame->decorativeOpacity, $contentBody);
        }

        // 3. White rounded rect + real QR, clipped to shape, offset for non-symmetric shapes
        $qrScale = $qrArea / $view;
        $svg .= sprintf('<g clip-path="url(#%s)">', $clipId);
        $svg .= sprintf(
            '<rect x="%.4F" y="%.4F" width="%.4F" height="%.4F" rx="%.4F" fill="%s"/>',
            $margin, $margin + $vOffset, $qrArea, $qrArea, $qrArea * 0.04, $bg
        );
        $svg .= sprintf(
            '<g transform="translate(%.4F,%.4F) scale(%.6F)">%s</g>',
            $margin, $margin + $vOffset, $qrScale, $contentBody
        );
        $svg .= '</g>';

        if ($frame->label !== null) {
            $color = $this->escape($frame->labelColor ?? $style->foreground);
            $svg .= sprintf(
                '<text x="%.4F" y="%.4F" text-anchor="middle" font-family="sans-serif" font-size="%.4F" fill="%s">%s</text>',
                $view / 2,
                $view + $labelHeight * 0.72,
                $labelHeight * 0.55,
                $color,
                $this->escape($frame->label)
            );
        }

        return $svg.'</svg>';
    }

    private function inscribeRatio(FrameShape $shape): float
    {
        return match ($shape) {
            FrameShape::Circle => 0.80,
            FrameShape::RoundedSquare => 0.88,
            FrameShape::Heart => 0.60,
            FrameShape::Star => 0.50,
            FrameShape::Hexagon => 0.75,
            FrameShape::Security => 0.85,
            FrameShape::None => 1.0,
        };
    }

    /**
     * Vertical offset to visually center the QR inside non-symmetric shapes.
     * Negative = move up.
     */
    private function verticalOffset(FrameShape $shape): float
    {
        return match ($shape) {
            FrameShape::Heart => -0.08,
            FrameShape::Star => 0.0,
            FrameShape::Circle => 0.0,
            FrameShape::RoundedSquare => 0.0,
            FrameShape::Hexagon => 0.0,
            FrameShape::Security => 0.0,
            FrameShape::None => 0.0,
        };
    }

    private function shapeElement(FrameShape $shape, int $view): string
    {
        return match ($shape) {
            FrameShape::Circle => sprintf('<circle cx="%1$.4F" cy="%1$.4F" r="%1$.4F"/>', $view / 2),
            FrameShape::RoundedSquare => sprintf('<rect width="%1$d" height="%1$d" rx="%2$.4F"/>', $view, $view * 0.18),
            FrameShape::Heart => $this->scaledPath('M 0.5 0.92 C 0.42 0.84 0.08 0.62 0.08 0.32 C 0.08 0.13 0.22 0.05 0.36 0.05 C 0.45 0.05 0.5 0.12 0.5 0.12 C 0.5 0.12 0.55 0.05 0.64 0.05 C 0.78 0.05 0.92 0.13 0.92 0.32 C 0.92 0.62 0.58 0.84 0.5 0.92 Z', $view),
            FrameShape::Star => $this->scaledPath('M 0.500 0.050 C 0.527 0.050 0.552 0.065 0.564 0.090 L 0.641 0.246 L 0.813 0.271 C 0.840 0.275 0.863 0.294 0.871 0.320 C 0.879 0.346 0.872 0.375 0.852 0.394 L 0.728 0.515 L 0.757 0.686 C 0.762 0.713 0.751 0.740 0.729 0.756 C 0.707 0.772 0.678 0.774 0.654 0.761 L 0.500 0.680 L 0.346 0.761 C 0.322 0.774 0.293 0.772 0.271 0.756 C 0.249 0.740 0.238 0.713 0.243 0.686 L 0.272 0.515 L 0.148 0.394 C 0.128 0.375 0.121 0.346 0.129 0.320 C 0.137 0.294 0.160 0.275 0.187 0.271 L 0.359 0.246 L 0.436 0.090 C 0.448 0.065 0.473 0.050 0.500 0.050 Z', $view),
            FrameShape::Hexagon => $this->scaledPath('M 0.5 0.02 L 0.95 0.27 L 0.95 0.73 L 0.5 0.98 L 0.05 0.73 L 0.05 0.27 Z', $view),
            FrameShape::Security => '',
            FrameShape::None => '',
        };
    }

    private function scaledPath(string $unitPath, int $view): string
    {
        return sprintf('<path d="%s" transform="scale(%d)"/>', $unitPath, $view);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
