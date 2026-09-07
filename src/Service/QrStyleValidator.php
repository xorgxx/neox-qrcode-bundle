<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEffect;
use Xorgxx\NeoxQrCodeBundle\Enum\FinderEyeShape;
use Xorgxx\NeoxQrCodeBundle\Enum\FrameShape;
use Xorgxx\NeoxQrCodeBundle\Enum\GradientType;
use Xorgxx\NeoxQrCodeBundle\Enum\ModuleShape;
use Xorgxx\NeoxQrCodeBundle\Enum\ReadabilityProfile;
use Xorgxx\NeoxQrCodeBundle\Model\QrFrameStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrValidationReport;

final class QrStyleValidator
{
    public function validate(QrStyle $style, ErrorCorrection $errorCorrection = ErrorCorrection::High, ?QrFrameStyle $frame = null, ReadabilityProfile $profile = ReadabilityProfile::Balanced): QrValidationReport
    {
        $errors = [];
        $warnings = [];
        $ratio = $this->contrastRatio($style->foreground, $style->background);

        if ($ratio < 3.0) {
            $errors[] = 'Foreground/background contrast is too low for reliable scanning.';
        } elseif ($ratio < 4.5) {
            $warnings[] = 'Contrast is usable but 4.5:1 or higher is safer.';
        }

        if ($style->margin < 4) {
            $warnings[] = 'A quiet zone of at least 4 modules is recommended.';
        }

        if ($style->moduleScale < 0.70) {
            $warnings[] = 'Small modules can reduce scan reliability.';
        }

        if (in_array($style->moduleShape, [ModuleShape::Liquid, ModuleShape::Blob, ModuleShape::Wave, ModuleShape::Cross], true)) {
            $warnings[] = sprintf(
                '%s modules connect neighboring data cells; scan-test the result at every intended output size.',
                ucfirst($style->moduleShape->value),
            );
        }

        if (null !== $style->logoHref) {
            if (ErrorCorrection::High !== $errorCorrection) {
                $warnings[] = 'Use error correction H when displaying a central logo.';
            }
            if ($style->logoScale > 0.24) {
                $warnings[] = 'The logo is large; test the QR on several devices.';
            }
        }

        $eyeShape = $style->finderEyeShape->value ?? $style->finderShape->value;
        if (in_array($eyeShape, [FinderEyeShape::Star->value, FinderEyeShape::Leaf->value, FinderEyeShape::Heart->value, FinderEyeShape::Flower->value], true)) {
            $warnings[] = 'Decorative finder eyes must be scan-tested on real devices.';
        }

        $score = $this->readabilityScore($style, $errorCorrection, $frame, $ratio, [] === $errors);

        return new QrValidationReport(
            [] === $errors,
            $errors,
            $warnings,
            $ratio,
            $score,
            [
                'margin' => $style->margin,
                'moduleScale' => $style->moduleScale,
                'moduleShape' => $style->moduleShape->value,
                'finderEyeShape' => $eyeShape,
                'finderEyeScale' => $style->finderEyeScale,
                'errorCorrection' => $errorCorrection->value,
                'hasLogo' => null !== $style->logoHref,
                'hasFinderIcon' => null !== $style->finderIconHref,
                'hasDecorativeFrame' => null !== $frame && FrameShape::None !== $frame->shape,
                'testProfile' => $profile->value,
                'testSizes' => implode(', ', $profile->sizes()),
            ],
        );
    }

    private function readabilityScore(QrStyle $style, ErrorCorrection $errorCorrection, ?QrFrameStyle $frame, float $contrastRatio, bool $valid): int
    {
        $score = 100;

        if ($contrastRatio < 3.0) {
            $score -= min(70, (int) round(50 + (3.0 - $contrastRatio) * 10));
        } elseif ($contrastRatio < 4.5) {
            $score -= (int) round((4.5 - $contrastRatio) * 10);
        } elseif ($contrastRatio < 7.0) {
            $score -= 3;
        }

        $score -= max(0, 4 - $style->margin) * 7;

        if ($style->moduleScale < 0.70) {
            $score -= 8 + (int) round((0.70 - $style->moduleScale) * 50);
        } elseif ($style->moduleScale < 0.85) {
            $score -= 4;
        }

        $score -= match ($style->moduleShape) {
            ModuleShape::Square, ModuleShape::Rounded => 0,
            ModuleShape::Dot => 3,
            ModuleShape::Blob => 4,
            ModuleShape::Liquid => 5,
            ModuleShape::Wave => 7,
            ModuleShape::Diamond => 7,
            ModuleShape::Cross => 8,
            ModuleShape::Heart => 10,
        };

        $eyeShape = $style->finderEyeShape->value ?? $style->finderShape->value;
        $score -= match ($eyeShape) {
            FinderEyeShape::Star->value => 7,
            FinderEyeShape::Leaf->value => 5,
            FinderEyeShape::Heart->value => 5,
            FinderEyeShape::Flower->value => 4,
            FinderEyeShape::Diamond->value => 3,
            FinderEyeShape::Hexagon->value => 2,
            FinderEyeShape::Shield->value => 2,
            FinderEyeShape::Octagon->value => 1,
            default => 0,
        };
        $score -= max(0, (int) round(($style->finderEyeScale - 1.0) * 20));

        if (null !== $style->logoHref) {
            $score -= 6;
            $score -= ErrorCorrection::High === $errorCorrection ? 0 : 10;
            $score -= max(0, (int) round(($style->logoScale - 0.24) * 100));
        }

        if (null !== $style->finderIconHref) {
            $score -= 6 + max(0, (int) round(($style->finderIconScale - 0.60) * 10));
        }

        $score -= match ($style->finderEffect) {
            FinderEffect::None => 0,
            FinderEffect::Shadow => 1,
            FinderEffect::Gradient => 3,
            FinderEffect::DoubleStroke => 4,
            FinderEffect::Dashed => 5,
        };

        if (GradientType::None !== $style->gradientType) {
            $score -= 3;
        }

        if (null !== $frame && FrameShape::None !== $frame->shape) {
            $score -= 8;
        }

        $score -= match ($errorCorrection) {
            ErrorCorrection::High => 0,
            ErrorCorrection::Quartile => 1,
            ErrorCorrection::Medium => 3,
            ErrorCorrection::Low => 5,
        };

        $score = max(0, min(100, $score));

        return $valid ? $score : min(49, $score);
    }

    private function contrastRatio(string $a, string $b): float
    {
        $l1 = $this->luminance($a);
        $l2 = $this->luminance($b);
        $light = max($l1, $l2);
        $dark = min($l1, $l2);

        return ($light + 0.05) / ($dark + 0.05);
    }

    private function luminance(string $hex): float
    {
        $rgb = [
            hexdec(substr($hex, 1, 2)) / 255,
            hexdec(substr($hex, 3, 2)) / 255,
            hexdec(substr($hex, 5, 2)) / 255,
        ];

        $rgb = array_map(static fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4, $rgb);

        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }
}
