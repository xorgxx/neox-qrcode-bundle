# Étendre le package

## Nouvelle forme de module

Ajoutez un cas à `ModuleShape`, puis implémentez sa primitive SVG dans `SvgRenderer::renderModule()`.

## Nouveau preset

```php
$presets->register('brand', new QrStyle(...));
```

## Autre format de sortie

Conservez SVG comme renderer canonique et ajoutez un autre adaptateur raster/export, ou implémentez un nouveau renderer autour de `QrMatrix`.

## Nouvelle stratégie de payload sécurisé

Ne modifiez pas `QrCodeGenerator`. Construisez/validez le payload dans un service séparé, puis passez la chaîne résultante à `QrCodeGenerator`.

## Remplacer le store de tokens à usage unique

Implémentez `SingleUseTokenStoreInterface` et câblez-le :

```php
// App\Security\RedisTokenStore
final class RedisTokenStore implements \Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface
{
    public function consume(string $jti): bool
    {
        // SETNX atomique dans Redis
    }

    public function isConsumed(string $jti): bool
    {
        // EXISTS dans Redis
    }
}
```

```yaml
# config/services.yaml
Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface:
    class: App\Security\RedisTokenStore
```
