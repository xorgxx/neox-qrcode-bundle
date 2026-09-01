<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Model\QrStyle;
use Xorgxx\NeoxQrCodeBundle\Model\QrValidationReport;

final class QrStyleValidator
{
    public function validate(QrStyle $style, ErrorCorrection $errorCorrection = ErrorCorrection::High): QrValidationReport
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

        if ($style->logoHref !== null) {
            if ($errorCorrection !== ErrorCorrection::High) {
                $warnings[] = 'Use error correction H when displaying a central logo.';
            }
            if ($style->logoScale > 0.24) {
                $warnings[] = 'The logo is large; test the QR on several devices.';
            }
        }

        return new QrValidationReport($errors === [], $errors, $warnings, $ratio);
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
