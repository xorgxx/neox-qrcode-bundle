<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Renderer;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEyeShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderFrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Model\QrMatrix;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry;

final class SvgRenderer
{
    public function __construct(
        private readonly ShapeRegistry $shapes,
    ) {
    }

    public function render(QrMatrix $matrix, QrStyle $style): string
    {
        [$content, $view] = $this->renderContent($matrix, $style);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" width="%2$d" height="%2$d" role="img" aria-label="QR Code">%3$s</svg>',
            $view,
            $style->size,
            $content
        );
    }

    /** @return array{string,int} */
    public function renderContent(QrMatrix $matrix, QrStyle $style): array
    {
        $n = $matrix->size();
        $view = $n + 2 * $style->margin;
        $fg = $this->escape($style->foreground);
        $bg = $this->escape($style->background);
        $finderColor = $this->escape($style->finderColor ?? $style->foreground);
        $alignmentColor = $this->escape($style->alignmentColor ?? $style->finderColor ?? $style->foreground);
        $modulePaint = $fg;
        $finderPaint = $finderColor;
        $alignmentPositions = $this->alignmentPatternCenters($n);
        // Finder frames are always rendered as a canonical unified ring.
        // Legacy decorative finderShape values are mapped to a safe frame and decorative eye.
        $skipFinderModules = true;

        $uid = bin2hex(random_bytes(4));
        $gradientId = 'neoxQrGradient_'.$uid;
        $finderGradientId = 'neoxFinderGradient_'.$uid;
        $liquidFilterId = 'neoxLiquidFilter_'.$uid;

        $svg = '<defs>';
        if (GradientType::None !== $style->gradientType && null !== $style->gradientTo) {
            $to = $this->escape($style->gradientTo);
            $svg .= match ($style->gradientType) {
                GradientType::Linear => sprintf('<linearGradient id="%s" x1="0%%" y1="0%%" x2="100%%" y2="100%%"><stop offset="0%%" stop-color="%s"/><stop offset="100%%" stop-color="%s"/></linearGradient>', $gradientId, $fg, $to),
                GradientType::Radial => sprintf('<radialGradient id="%s" cx="50%%" cy="50%%" r="70%%"><stop offset="0%%" stop-color="%s"/><stop offset="100%%" stop-color="%s"/></radialGradient>', $gradientId, $fg, $to),
            };
            $modulePaint = 'url(#'.$gradientId.')';
        }
        if (FinderEffect::Gradient === $style->finderEffect && null !== $style->finderGradientTo) {
            $fTo = $this->escape($style->finderGradientTo);
            $svg .= sprintf('<radialGradient id="%s" cx="50%%" cy="50%%" r="70%%"><stop offset="0%%" stop-color="%s"/><stop offset="100%%" stop-color="%s"/></radialGradient>', $finderGradientId, $finderColor, $fTo);
            $finderPaint = 'url(#'.$finderGradientId.')';
        }
        if (ModuleShape::Liquid === $style->moduleShape) {
            $svg .= sprintf('<filter id="%s" x="-10%%" y="-10%%" width="120%%" height="120%%"><feGaussianBlur in="SourceGraphic" stdDeviation="0.4"/><feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -9"/></filter>', $liquidFilterId);
        }
        $svg .= '</defs>';

        $svg .= sprintf('<rect width="100%%" height="100%%" fill="%s"/>', $bg);
        $svg .= $this->renderFinderShadow($n, $style);
        $svg .= '<g shape-rendering="geometricPrecision">';

        if (ModuleShape::Liquid === $style->moduleShape) {
            $svg .= $this->renderLiquidModules($matrix, $n, $style, $modulePaint, $alignmentColor, $alignmentPositions, $skipFinderModules, $finderPaint, $liquidFilterId);
        } elseif (in_array($style->moduleShape, [ModuleShape::Blob, ModuleShape::Wave, ModuleShape::Cross], true)) {
            $svg .= $this->renderConnectedModules($matrix, $n, $style, $modulePaint, $alignmentPositions);
        } else {
            for ($y = 0; $y < $n; ++$y) {
                for ($x = 0; $x < $n; ++$x) {
                    if (!$matrix->isDark($x, $y)) {
                        continue;
                    }

                    $px = $x + $style->margin;
                    $py = $y + $style->margin;

                    if ($this->isFinderArea($x, $y, $n)) {
                        continue;
                    }

                    if ($this->isAlignmentArea($x, $y, $alignmentPositions)) {
                        continue;
                    }

                    $svg .= $this->renderModule($px, $py, $style->moduleShape, $modulePaint, $style->moduleScale);
                }
            }
        }

        $svg .= '</g>';
        $svg .= $this->renderAlignmentRings($alignmentPositions, $style, $alignmentColor);
        $svg .= $this->renderFinderRings($n, $style, $finderPaint);
        $svg .= $this->renderFinderIcons($n, $style);
        $svg .= $this->renderFinderOutline($n, $style, $finderColor);
        $svg .= $this->renderLogo($view, $style);

        return [$svg, $view];
    }

    private function renderLogo(int $view, QrStyle $style): string
    {
        if (null === $style->logoHref) {
            return '';
        }

        $logoSize = $view * $style->logoScale;
        $x = ($view - $logoSize) / 2;
        $y = ($view - $logoSize) / 2;
        $href = $this->escape($style->logoHref);
        $svg = '';

        if ($style->logoBackground) {
            $pad = $logoSize * 0.14;
            $svg .= sprintf(
                '<rect x="%.4F" y="%.4F" width="%.4F" height="%.4F" rx="%.4F" fill="%s"/>',
                $x - $pad,
                $y - $pad,
                $logoSize + 2 * $pad,
                $logoSize + 2 * $pad,
                $logoSize * 0.16,
                $this->escape($style->background)
            );
        }

        $svg .= sprintf(
            '<image href="%s" x="%.4F" y="%.4F" width="%.4F" height="%.4F" preserveAspectRatio="xMidYMid meet"/>',
            $href,
            $x,
            $y,
            $logoSize,
            $logoSize
        );

        return $svg;
    }

    private function renderModule(float $x, float $y, ModuleShape $shape, string $paint, float $scale): string
    {
        $pad = (1 - $scale) / 2;

        return $this->shapes->renderModule($shape, $x + $pad, $y + $pad, $scale, $paint);
    }

    /**
     * Renders data modules as truly merged liquid shapes using an SVG
     * metaball filter (Gaussian blur + alpha threshold). All touching
     * modules — horizontally, vertically, and diagonally — fuse into a
     * single continuous blob with smooth rounded boundaries.
     *
     * @param array<int, array{int,int}> $alignmentPositions
     */
    private function renderLiquidModules(
        QrMatrix $matrix,
        int $n,
        QrStyle $style,
        string $modulePaint,
        string $alignmentColor,
        array $alignmentPositions,
        bool $skipFinderModules,
        string $finderPaint,
        string $liquidFilterId,
    ): string {
        $dataSvg = '';
        $otherSvg = '';

        for ($y = 0; $y < $n; ++$y) {
            for ($x = 0; $x < $n; ++$x) {
                if (!$matrix->isDark($x, $y)) {
                    continue;
                }

                $px = $x + $style->margin;
                $py = $y + $style->margin;

                if ($this->isFinderArea($x, $y, $n)) {
                    if (!$skipFinderModules) {
                        $otherSvg .= $this->renderFinderCell($px, $py, $style->finderShape, $finderPaint, $style->moduleScale);
                    }
                    continue;
                }

                if ($this->isAlignmentArea($x, $y, $alignmentPositions)) {
                    continue;
                }

                $dataSvg .= sprintf('<rect x="%.4F" y="%.4F" width="1" height="1" fill="%s"/>', $px, $py, $modulePaint);
            }
        }

        return '<g filter="url(#'.$liquidFilterId.')">'.$dataSvg.'</g>'.$otherSvg;
    }

    /**
     * Draws topology-aware module shapes and bridges only orthogonally
     * adjacent dark data cells. Functional QR patterns remain untouched.
     *
     * @param array<int, array{int,int}> $alignmentPositions
     */
    private function renderConnectedModules(
        QrMatrix $matrix,
        int $n,
        QrStyle $style,
        string $paint,
        array $alignmentPositions,
    ): string {
        $bridges = '';
        $modules = '';
        $scale = $style->moduleScale;
        $nodeScale = match ($style->moduleShape) {
            ModuleShape::Blob => $scale,
            ModuleShape::Wave => $scale * 0.76,
            ModuleShape::Cross => $scale,
            default => $scale,
        };
        $bridgeWidth = match ($style->moduleShape) {
            ModuleShape::Blob => $scale * 0.56,
            ModuleShape::Wave => $scale * 0.30,
            ModuleShape::Cross => $scale * 0.24,
            default => $scale * 0.40,
        };

        for ($y = 0; $y < $n; ++$y) {
            for ($x = 0; $x < $n; ++$x) {
                if (!$this->isDataModule($matrix, $x, $y, $n, $alignmentPositions)) {
                    continue;
                }

                $cx = $x + $style->margin + 0.5;
                $cy = $y + $style->margin + 0.5;
                $pad = (1.0 - $nodeScale) / 2;

                $modules .= $this->shapes->renderModule(
                    $style->moduleShape,
                    $x + $style->margin + $pad,
                    $y + $style->margin + $pad,
                    $nodeScale,
                    $paint,
                );

                if ($this->isDataModule($matrix, $x + 1, $y, $n, $alignmentPositions)) {
                    $bridges .= $this->renderModuleBridge($style->moduleShape, $cx, $cy, $cx + 1, $cy, $bridgeWidth, $paint, 0 === ($x + $y) % 2);
                }
                if ($this->isDataModule($matrix, $x, $y + 1, $n, $alignmentPositions)) {
                    $bridges .= $this->renderModuleBridge($style->moduleShape, $cx, $cy, $cx, $cy + 1, $bridgeWidth, $paint, 0 === ($x + $y) % 2);
                }
            }
        }

        return sprintf('<g data-module-shape="%s">%s%s</g>', $style->moduleShape->value, $bridges, $modules);
    }

    private function renderModuleBridge(
        ModuleShape $shape,
        float $x1,
        float $y1,
        float $x2,
        float $y2,
        float $width,
        string $paint,
        bool $positiveCurve,
    ): string {
        if (ModuleShape::Wave === $shape) {
            $offset = $positiveCurve ? 0.18 : -0.18;
            $cx = ($x1 + $x2) / 2 + (abs($y2 - $y1) > 0 ? $offset : 0);
            $cy = ($y1 + $y2) / 2 + (abs($x2 - $x1) > 0 ? $offset : 0);

            return sprintf(
                '<path d="M %.4F %.4F Q %.4F %.4F %.4F %.4F" fill="none" stroke="%s" stroke-width="%.4F" stroke-linecap="round"/>',
                $x1,
                $y1,
                $cx,
                $cy,
                $x2,
                $y2,
                $paint,
                $width,
            );
        }

        return sprintf(
            '<path d="M %.4F %.4F L %.4F %.4F" fill="none" stroke="%s" stroke-width="%.4F" stroke-linecap="round"/>',
            $x1,
            $y1,
            $x2,
            $y2,
            $paint,
            $width,
        );
    }

    /** @param array<int, array{int,int}> $alignmentPositions */
    private function isDataModule(QrMatrix $matrix, int $x, int $y, int $n, array $alignmentPositions): bool
    {
        return $x >= 0
            && $y >= 0
            && $x < $n
            && $y < $n
            && $matrix->isDark($x, $y)
            && !$this->isFinderArea($x, $y, $n)
            && !$this->isAlignmentArea($x, $y, $alignmentPositions);
    }

    private function renderFinderCell(float $x, float $y, FinderShape $shape, string $color, float $scale): string
    {
        $s = match ($shape) {
            FinderShape::Leaf, FinderShape::Star => 1.0,
            FinderShape::Dotted => min($scale, .55),
            default => max($scale, .96),
        };

        return $this->shapes->renderFinder($shape, $x, $y, $s, $color);
    }

    /** @return array<int, array{int,int}> */
    private function finderAnchors(int $n): array
    {
        return [[0, 0], [$n - 7, 0], [0, $n - 7]];
    }

    /** Renders a safe 7x7/5x5 frame with a separately styled 3x3 eye. */
    private function renderFinderRings(int $n, QrStyle $style, string $color): string
    {
        $bg = $this->escape($style->background);
        $frameShape = $this->resolveFinderFrameShape($style);
        $eyeShape = $this->resolveFinderEyeShape($style);

        $svg = '';
        foreach ($this->finderAnchors($n) as [$fx, $fy]) {
            $x = $fx + $style->margin;
            $y = $fy + $style->margin;

            $svg .= $this->shapes->renderFinderFrame($frameShape, $x, $y, 7.0, $color);
            $svg .= $this->shapes->renderFinderFrame($frameShape, $x + 1, $y + 1, 5.0, $bg);

            if (null !== $eyeShape) {
                $eyeSize = $this->finderEyeSize($eyeShape) * $style->finderEyeScale;
                $eyeOffset = (7.0 - $eyeSize) / 2;
                $svg .= $this->shapes->renderFinderEye($eyeShape, $x + $eyeOffset, $y + $eyeOffset, $eyeSize, $color);
            } elseif (null !== $style->finderCenterShape) {
                $svg .= $this->renderEyeShape($x + 2, $y + 2, 3.0, $style->finderCenterShape, $color);
            } else {
                $svg .= $this->renderEyeShape($x + 2, $y + 2, 3.0, ModuleShape::Square, $color);
            }
        }

        return $svg;
    }

    private function finderEyeSize(FinderEyeShape $shape): float
    {
        return match ($shape) {
            FinderEyeShape::Square, FinderEyeShape::Rounded, FinderEyeShape::Circle => 3.0,
            default => 3.2,
        };
    }

    private function resolveFinderFrameShape(QrStyle $style): FinderFrameShape
    {
        if (null !== $style->finderFrameShape) {
            return $style->finderFrameShape;
        }

        return match ($style->finderShape) {
            FinderShape::Rounded => FinderFrameShape::Rounded,
            FinderShape::Circle => FinderFrameShape::Circle,
            default => FinderFrameShape::Square,
        };
    }

    private function resolveFinderEyeShape(QrStyle $style): ?FinderEyeShape
    {
        if (null !== $style->finderEyeShape) {
            return FinderEyeShape::tryFrom($style->finderEyeShape->value);
        }

        if (null !== $style->finderCenterShape) {
            return null;
        }

        return match ($style->finderShape) {
            FinderShape::Rounded => FinderEyeShape::Rounded,
            FinderShape::Circle => FinderEyeShape::Circle,
            FinderShape::Diamond => FinderEyeShape::Diamond,
            FinderShape::Leaf => FinderEyeShape::Leaf,
            FinderShape::Hexagon => FinderEyeShape::Hexagon,
            FinderShape::Star => FinderEyeShape::Star,
            default => FinderEyeShape::Square,
        };
    }

    private function renderEyeShape(float $x, float $y, float $size, ModuleShape $shape, string $color): string
    {
        return $this->shapes->renderModule($shape, $x, $y, $size, $color);
    }

    private function renderFinderShadow(int $n, QrStyle $style): string
    {
        if (FinderEffect::Shadow !== $style->finderEffect) {
            return '';
        }

        $svg = '';
        foreach ($this->finderAnchors($n) as [$fx, $fy]) {
            $x = $fx + $style->margin - 0.15;
            $y = $fy + $style->margin - 0.15 + 0.35;
            $svg .= sprintf('<rect x="%.4F" y="%.4F" width="7.3" height="7.3" rx="1.2" fill="#000000" opacity="0.25"/>', $x, $y);
        }

        return $svg;
    }

    private function renderFinderOutline(int $n, QrStyle $style, string $color): string
    {
        if (!in_array($style->finderEffect, [FinderEffect::DoubleStroke, FinderEffect::Dashed], true)) {
            return '';
        }

        $svg = '';
        foreach ($this->finderAnchors($n) as [$fx, $fy]) {
            $x = $fx + $style->margin - 0.2;
            $y = $fy + $style->margin - 0.2;

            if (FinderEffect::Dashed === $style->finderEffect) {
                $svg .= sprintf(
                    '<rect x="%.4F" y="%.4F" width="7.4" height="7.4" rx="1.2" fill="none" stroke="%s" stroke-width="0.3" stroke-dasharray="0.8 0.6"/>',
                    $x,
                    $y,
                    $color
                );
                continue;
            }

            $svg .= sprintf('<rect x="%.4F" y="%.4F" width="7.4" height="7.4" rx="1.2" fill="none" stroke="%s" stroke-width="0.25"/>', $x, $y, $color);
            $svg .= sprintf('<rect x="%.4F" y="%.4F" width="8.4" height="8.4" rx="1.5" fill="none" stroke="%s" stroke-width="0.2"/>', $x - 0.5, $y - 0.5, $color);
        }

        return $svg;
    }

    private function renderFinderIcons(int $n, QrStyle $style): string
    {
        if (null === $style->finderIconHref) {
            return '';
        }

        $href = $this->escape($style->finderIconHref);
        $size = 3 * $style->finderIconScale;
        $svg = '';

        foreach ($this->finderAnchors($n) as [$fx, $fy]) {
            $cx = $fx + $style->margin + 3.5;
            $cy = $fy + $style->margin + 3.5;
            $svg .= sprintf(
                '<image href="%s" x="%.4F" y="%.4F" width="%.4F" height="%.4F" preserveAspectRatio="xMidYMid meet"/>',
                $href,
                $cx - $size / 2,
                $cy - $size / 2,
                $size,
                $size
            );
        }

        return $svg;
    }

    /**
     * Renders alignment patterns as concentric solid shapes (5x5 ring,
     * 3x3 background cutout, 1x1 center dot) instead of individual modules.
     * Uses the same shape as the alignment shape selection for the outer
     * ring and center dot.
     *
     * @param array<int, array{int,int}> $centers
     */
    private function renderAlignmentRings(array $centers, QrStyle $style, string $color): string
    {
        if (empty($centers)) {
            return '';
        }

        $bg = $this->escape($style->background);
        $shape = $style->alignmentShape;
        $svg = '';

        foreach ($centers as [$cx, $cy]) {
            $x = ($cx - 2) + $style->margin;
            $y = ($cy - 2) + $style->margin;

            $svg .= $this->renderAlignmentRingBlock($x, $y, 5.0, $shape, $color);
            $svg .= $this->renderAlignmentRingBlock($x + 1, $y + 1, 3.0, $shape, $bg);
            $svg .= $this->renderAlignmentCenterDot($x + 2, $y + 2, 1.0, $shape, $color);
        }

        return $svg;
    }

    private function renderAlignmentRingBlock(float $x, float $y, float $size, AlignmentShape $shape, string $color): string
    {
        return $this->shapes->renderAlignment($shape, $x, $y, $size, $color);
    }

    private function renderAlignmentCenterDot(float $x, float $y, float $size, AlignmentShape $shape, string $color): string
    {
        return $this->shapes->renderAlignment($shape, $x, $y, $size, $color);
    }

    private function isFinderArea(int $x, int $y, int $n): bool
    {
        return ($x < 7 && $y < 7)
            || ($x >= $n - 7 && $y < 7)
            || ($x < 7 && $y >= $n - 7);
    }

    /** @param array<int, array{int,int}> $centers */
    private function isAlignmentArea(int $x, int $y, array $centers): bool
    {
        foreach ($centers as [$cx, $cy]) {
            if ($x >= $cx - 2 && $x <= $cx + 2 && $y >= $cy - 2 && $y <= $cy + 2) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, array{int,int}> */
    private function alignmentPatternCenters(int $n): array
    {
        $version = ($n - 21) / 4 + 1;
        if ($version < 2 || $version > 40) {
            return [];
        }

        $positions = $this->alignmentPositionList($version);
        $last = $positions[count($positions) - 1];
        $centers = [];

        foreach ($positions as $row) {
            foreach ($positions as $col) {
                if (6 === $row && 6 === $col) {
                    continue;
                }
                if (6 === $row && $col === $last) {
                    continue;
                }
                if ($row === $last && 6 === $col) {
                    continue;
                }
                $centers[] = [$row, $col];
            }
        }

        return $centers;
    }

    /** @return list<int> */
    private function alignmentPositionList(int $version): array
    {
        return match ($version) {
            1 => [],
            2 => [6, 18],
            3 => [6, 22],
            4 => [6, 26],
            5 => [6, 30],
            6 => [6, 34],
            7 => [6, 22, 38],
            8 => [6, 24, 42],
            9 => [6, 26, 46],
            10 => [6, 28, 50],
            11 => [6, 30, 54],
            12 => [6, 32, 58],
            13 => [6, 34, 62],
            14 => [6, 26, 46, 66],
            15 => [6, 26, 48, 70],
            16 => [6, 26, 50, 74],
            17 => [6, 30, 54, 78],
            18 => [6, 30, 56, 82],
            19 => [6, 30, 58, 86],
            20 => [6, 34, 62, 90],
            21 => [6, 28, 50, 72, 94],
            22 => [6, 26, 50, 74, 98],
            23 => [6, 30, 54, 78, 102],
            24 => [6, 28, 54, 80, 106],
            25 => [6, 32, 58, 84, 110],
            26 => [6, 30, 58, 86, 114],
            27 => [6, 34, 62, 90, 118],
            28 => [6, 26, 50, 74, 98, 122],
            29 => [6, 30, 54, 78, 102, 126],
            30 => [6, 26, 52, 78, 104, 130],
            31 => [6, 30, 56, 82, 108, 134],
            32 => [6, 34, 60, 86, 112, 138],
            33 => [6, 30, 58, 86, 114, 142],
            34 => [6, 34, 62, 90, 118, 146],
            35 => [6, 30, 54, 78, 102, 126, 150],
            36 => [6, 24, 50, 76, 102, 128, 154],
            37 => [6, 28, 54, 80, 106, 132, 158],
            38 => [6, 32, 58, 84, 110, 136, 162],
            39 => [6, 26, 54, 82, 110, 138, 166],
            40 => [6, 30, 58, 86, 114, 142, 170],
            default => [],
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
