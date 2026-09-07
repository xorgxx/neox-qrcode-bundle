# Extending the package

## New module shape

Add a case to `ModuleShape`, then declare its SVG primitive in `ShapeRegistry`. Do not modify the QR matrix.

## New preset

```php
$presets->register('brand', new QrStyle(...));
```

## Different image output

Keep SVG as the canonical renderer and add another raster/export adapter, or implement a new renderer around `QrMatrix`.

## Server-side verification decoder

The bundle uses `NullQrDecoder` by default and requires no decoding library. To enable real server-side verification, implement `QrDecoderInterface`, decode the SVG at the sizes supplied by `ReadabilityProfile`, then replace the service alias. The adapter must return a `QrDecodeReport` and compare decoded content with expected content.

```yaml
Xorgxx\NeoxQrCodeBundle\Decoder\QrDecoderInterface:
    class: App\QrCode\ServerQrDecoder
```

## New secure payload strategy

Do not change `QrCodeGenerator`. Build/verify the payload in a separate service, then pass the resulting string to `QrCodeGenerator`.

## Replacing the single-use token store

Implement `SingleUseTokenStoreInterface` and wire it:

```php
// App\Security\RedisTokenStore
final class RedisTokenStore implements \Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface
{
    public function consume(string $jti): bool
    {
        // Atomic SETNX in Redis
    }

    public function isConsumed(string $jti): bool
    {
        // EXISTS in Redis
    }
}
```

```yaml
# config/services.yaml
Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface:
    class: App\Security\RedisTokenStore
```
