<?php

declare(strict_types=1);

namespace Xorgxx\NeoxQrCodeBundle\Renderer;

final class PngRenderer
{
    public function fromSvg(string $svg, int $size): string
    {
        if (!class_exists(\Imagick::class)) {
            throw new \RuntimeException('PNG export requires the Imagick PHP extension.');
        }

        $image = new \Imagick();
        $image->setBackgroundColor(new \ImagickPixel('transparent'));
        $image->readImageBlob($svg);
        $image->setImageFormat('png32');
        $image->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, 1);
        $png = $image->getImagesBlob();
        $image->clear();
        $image->destroy();

        return $png;
    }
}
