<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use Xorgxx\NeoxQrCodeBundle\Enum\AlignmentShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;

/**
 * Central registry of all SVG shape definitions used by the QR code renderer.
 *
 * Every shape is defined once as a data array (type + parameters).
 * A single renderShape() method generates the SVG element for any shape
 * at any position, size and color — no duplicated match blocks.
 *
 * To add a new shape:
 *   1. Add the case to the relevant enum.
 *   2. Add its definition in $moduleShapes / $finderShapes / $alignmentShapes.
 *   3. If path-based, add the path data to $paths.
 *   4. No other change needed.
 */
final class ShapeRegistry
{
    private const TYPE_RECT    = 'rect';
    private const TYPE_CIRCLE  = 'circle';
    private const TYPE_DIAMOND = 'diamond';
    private const TYPE_PATH    = 'path';
    private const TYPE_NONE    = 'none';

    /**
     * Normalized SVG path data (coordinates 0–1) for path-based shapes.
     *
     * @var array<string, string>
     */
//    private array $paths = [
//        'heart'   => 'M 0.5 0.92 C 0.42 0.84 0.08 0.62 0.08 0.32 C 0.08 0.13 0.22 0.05 0.36 0.05 C 0.45 0.05 0.5 0.12 0.5 0.12 C 0.5 0.12 0.55 0.05 0.64 0.05 C 0.78 0.05 0.92 0.13 0.92 0.32 C 0.92 0.62 0.58 0.84 0.5 0.92 Z',
//        'leaf'    => 'M 0.5 0.03 C 0.82 0.18 0.92 0.50 0.5 0.97 C 0.08 0.50 0.18 0.18 0.5 0.03 Z',
//        'hexagon' => 'M 0.5 0.06 L 0.92 0.30 L 0.92 0.70 L 0.5 0.94 L 0.08 0.70 L 0.08 0.30 Z',
//        'star'    => 'M 0.5 0.05 L 0.61 0.35 L 0.95 0.35 L 0.67 0.55 L 0.78 0.90 L 0.5 0.70 L 0.22 0.90 L 0.33 0.55 L 0.05 0.35 L 0.39 0.35 Z',
//    ];
    private array $paths = [
        'heart' => '
            M 0.50 0.92
            C 0.44 0.86 0.08 0.64 0.08 0.34
            C 0.08 0.14 0.22 0.06 0.36 0.06
            C 0.44 0.06 0.50 0.11 0.50 0.18
            C 0.50 0.11 0.56 0.06 0.64 0.06
            C 0.78 0.06 0.92 0.14 0.92 0.34
            C 0.92 0.64 0.56 0.86 0.50 0.92
            Z
        ',

        'leaf' => '
            M 0.10 0.90
            C 0.12 0.50 0.28 0.18 0.88 0.08
            C 0.90 0.62 0.62 0.88 0.10 0.90
            Z
        ',

        'hexagon' => '
            M 0.25 0.07
            L 0.75 0.07
            L 0.96 0.50
            L 0.75 0.93
            L 0.25 0.93
            L 0.04 0.50
            Z
        ',

        'star' => '
            M 0.50 0.04
            L 0.61 0.35
            L 0.95 0.35
            L 0.68 0.55
            L 0.79 0.89
            L 0.50 0.69
            L 0.21 0.89
            L 0.32 0.55
            L 0.05 0.35
            L 0.39 0.35
            Z
        ',

        'cross' => '
            M 0.35 0.08
            L 0.65 0.08
            L 0.65 0.35
            L 0.92 0.35
            L 0.92 0.65
            L 0.65 0.65
            L 0.65 0.92
            L 0.35 0.92
            L 0.35 0.65
            L 0.08 0.65
            L 0.08 0.35
            L 0.35 0.35
            Z
        ',

        'diamond' => '
            M 0.50 0.04
            L 0.96 0.50
            L 0.50 0.96
            L 0.04 0.50
            Z
        ',

        'drop' => '
            M 0.50 0.05
            C 0.50 0.05 0.86 0.48 0.86 0.68
            C 0.86 0.88 0.70 0.96 0.50 0.96
            C 0.30 0.96 0.14 0.88 0.14 0.68
            C 0.14 0.48 0.50 0.05 0.50 0.05
            Z
        ',

        'triangle' => '
            M 0.50 0.06
            L 0.95 0.92
            L 0.05 0.92
            Z
        ',

        'shield' => '
            M 0.50 0.05
            L 0.88 0.18
            L 0.84 0.58
            C 0.80 0.76 0.66 0.88 0.50 0.96
            C 0.34 0.88 0.20 0.76 0.16 0.58
            L 0.12 0.18
            Z
        ',

        'chevron' => '
            M 0.08 0.18
            L 0.50 0.58
            L 0.92 0.18
            L 0.92 0.55
            L 0.50 0.92
            L 0.08 0.55
            Z
        ',
    ];

    /**
     * Corner radius ratios (relative to size) for rounded rect shapes.
     *
     * @var array<string, float>
     */
    private array $cornerRadii = [
        'rounded'        => 0.35,
        'rounded-finder' => 0.28,
    ];

    /**
     * Module shape definitions keyed by enum value.
     *
     * @var array<string, array{type: string, path?: string, radius?: string}>
     */
    private array $moduleShapes = [
        'square'  => ['type' => self::TYPE_RECT],
        'rounded' => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
        'dot'     => ['type' => self::TYPE_CIRCLE],
        'diamond' => ['type' => self::TYPE_DIAMOND],
        'heart'   => ['type' => self::TYPE_PATH, 'path' => 'heart'],
        'liquid'  => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
    ];

    /**
     * Finder shape definitions keyed by enum value.
     *
     * @var array<string, array{type: string, path?: string, radius?: string}>
     */
    private array $finderShapes = [
        'square'   => ['type' => self::TYPE_RECT],
        'rounded'  => ['type' => self::TYPE_RECT, 'radius' => 'rounded-finder'],
        'circle'   => ['type' => self::TYPE_CIRCLE],
        'diamond'  => ['type' => self::TYPE_DIAMOND],
        'leaf'     => ['type' => self::TYPE_PATH, 'path' => 'leaf'],
        'hexagon'  => ['type' => self::TYPE_PATH, 'path' => 'hexagon'],
        'star'     => ['type' => self::TYPE_PATH, 'path' => 'star'],
        'dotted'   => ['type' => self::TYPE_CIRCLE],
        'minimal'  => ['type' => self::TYPE_NONE],
        'inverted' => ['type' => self::TYPE_NONE],
    ];

    /**
     * Alignment shape definitions keyed by enum value.
     *
     * @var array<string, array{type: string, path?: string, radius?: string}>
     */
    private array $alignmentShapes = [
        'square'  => ['type' => self::TYPE_RECT],
        'rounded' => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
        'circle'  => ['type' => self::TYPE_CIRCLE],
        'diamond' => ['type' => self::TYPE_DIAMOND],
        'leaf'    => ['type' => self::TYPE_PATH, 'path' => 'leaf'],
        'dot'     => ['type' => self::TYPE_CIRCLE],
    ];

    public function renderModule(ModuleShape $shape, float $x, float $y, float $s, string $paint): string
    {
        $def = $this->moduleShapes[$shape->value] ?? null;

        return $def ? $this->renderShape($def, $x, $y, $s, $paint) : '';
    }

    public function renderFinder(FinderShape $shape, float $x, float $y, float $size, string $color): string
    {
        $def = $this->finderShapes[$shape->value] ?? null;

        return $def ? $this->renderShape($def, $x, $y, $size, $color) : '';
    }

    public function renderAlignment(AlignmentShape $shape, float $x, float $y, float $size, string $color): string
    {
        $def = $this->alignmentShapes[$shape->value] ?? null;

        return $def ? $this->renderShape($def, $x, $y, $size, $color) : '';
    }

    public function getPath(string $name): ?string
    {
        return $this->paths[$name] ?? null;
    }

    public function setPath(string $name, string $pathData): void
    {
        $this->paths[$name] = $pathData;
    }

    public function setCornerRadius(string $name, float $ratio): void
    {
        $this->cornerRadii[$name] = $ratio;
    }

    /**
     * Registers a new module shape definition at runtime.
     *
     * @param array{type: string, path?: string, radius?: string} $definition
     */
    public function registerModuleShape(string $enumValue, array $definition): void
    {
        $this->moduleShapes[$enumValue] = $definition;
    }

    /**
     * Registers a new finder shape definition at runtime.
     *
     * @param array{type: string, path?: string, radius?: string} $definition
     */
    public function registerFinderShape(string $enumValue, array $definition): void
    {
        $this->finderShapes[$enumValue] = $definition;
    }

    /**
     * Registers a new alignment shape definition at runtime.
     *
     * @param array{type: string, path?: string, radius?: string} $definition
     */
    public function registerAlignmentShape(string $enumValue, array $definition): void
    {
        $this->alignmentShapes[$enumValue] = $definition;
    }

    /**
     * Single method that renders any shape definition to SVG.
     *
     * @param array{type: string, path?: string, radius?: string} $def
     */
    private function renderShape(array $def, float $x, float $y, float $s, string $paint): string
    {
        $cx = $x + $s / 2;
        $cy = $y + $s / 2;

        return match ($def['type']) {
            self::TYPE_RECT => isset($def['radius'])
                ? sprintf('<rect x="%.4F" y="%.4F" width="%.4F" height="%.4F" rx="%.4F" fill="%s"/>', $x, $y, $s, $s, $s * $this->cornerRadii[$def['radius']], $paint)
                : sprintf('<rect x="%.4F" y="%.4F" width="%.4F" height="%.4F" fill="%s"/>', $x, $y, $s, $s, $paint),

            self::TYPE_CIRCLE => sprintf('<circle cx="%.4F" cy="%.4F" r="%.4F" fill="%s"/>', $cx, $cy, $s / 2, $paint),

            self::TYPE_DIAMOND => sprintf(
                '<path d="M %.4F %.4F L %.4F %.4F L %.4F %.4F L %.4F %.4F Z" fill="%s"/>',
                $cx, $y, $x + $s, $cy, $cx, $y + $s, $x, $cy, $paint,
            ),

            self::TYPE_PATH => $this->renderPath($def['path'] ?? '', $x, $y, $s, $paint),

            self::TYPE_NONE => '',
        };
    }

    private function renderPath(string $name, float $x, float $y, float $s, string $paint): string
    {
        $path = $this->paths[$name] ?? '';

        return sprintf('<path d="%s" transform="translate(%.4F %.4F) scale(%.4F)" fill="%s"/>', $path, $x, $y, $s, $paint);
    }
}
