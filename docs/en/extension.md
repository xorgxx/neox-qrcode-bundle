# Extending the package

## New module shape

Add a case to `ModuleShape`, then implement its SVG primitive in `SvgRenderer::renderModule()`.

## New preset

```php
$presets->register('brand', new QrStyle(...));
```

## Different image output

Keep SVG as the canonical renderer and add another raster/export adapter, or implement a new renderer around `QrMatrix`.

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
