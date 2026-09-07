<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Decoder;

use Xorgxx\NeoxQrCodeBundle\Enum\ReadabilityProfile;
use Xorgxx\NeoxQrCodeBundle\Model\QrDecodeReport;

interface QrDecoderInterface
{
    public function decode(string $svg, string $expectedContent, ReadabilityProfile $profile): QrDecodeReport;
}
