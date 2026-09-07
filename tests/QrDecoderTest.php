<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Tests;

use PHPUnit\Framework\TestCase;
use Xorgxx\NeoxQrCodeBundle\Decoder\NullQrDecoder;
use Xorgxx\NeoxQrCodeBundle\Enum\ReadabilityProfile;

final class QrDecoderTest extends TestCase
{
    public function testReadabilityProfilesExposeExpectedSizes(): void
    {
        self::assertSame([96, 128, 256], ReadabilityProfile::Compact->sizes());
        self::assertSame([128, 256, 512], ReadabilityProfile::Balanced->sizes());
        self::assertSame([256, 512, 1024], ReadabilityProfile::Print->sizes());
    }

    public function testNullDecoderReportsUnavailable(): void
    {
        $report = (new NullQrDecoder())->decode('<svg/>', 'expected', ReadabilityProfile::Balanced);

        self::assertFalse($report->available);
        self::assertSame([], $report->successfulSizes);
        self::assertNotNull($report->message);
    }
}
