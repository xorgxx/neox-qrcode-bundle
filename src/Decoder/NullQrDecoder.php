<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Decoder;

use Xorgxx\NeoxQrCodeBundle\Enum\ReadabilityProfile;
use Xorgxx\NeoxQrCodeBundle\Model\QrDecodeReport;

final class NullQrDecoder implements QrDecoderInterface
{
    public function decode(string $svg, string $expectedContent, ReadabilityProfile $profile): QrDecodeReport
    {
        return new QrDecodeReport(false, message: 'No server-side QR decoder is configured.');
    }
}
