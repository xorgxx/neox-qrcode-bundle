<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Service;

use BaconQrCode\Encoder\Encoder;
use Xorgxx\NeoxQrCodeBundle\Enum\ErrorCorrection;
use Xorgxx\NeoxQrCodeBundle\Model\QrMatrix;

final class QrMatrixGenerator
{
    public function generate(string $content, ErrorCorrection $errorCorrection = ErrorCorrection::High): QrMatrix
    {
        $content = trim($content);
        if ($content === '') {
            throw new \InvalidArgumentException('QR content cannot be empty.');
        }
        if (mb_strlen($content) > 4096) {
            throw new \InvalidArgumentException('QR content is too long for this component safety limit.');
        }

        $code = Encoder::encode($content, $errorCorrection->toBacon(), Encoder::DEFAULT_BYTE_MODE_ENCODING, null);
        $matrix = $code->getMatrix();
        $width = $matrix->getWidth();
        $height = $matrix->getHeight();

        $cells = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $row[] = 1 === $matrix->get($x, $y);
            }
            $cells[] = $row;
        }

        return new QrMatrix($cells);
    }
}
