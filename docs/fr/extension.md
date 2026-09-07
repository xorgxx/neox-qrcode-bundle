# Étendre le package

## Nouvelle forme de module

Ajoutez un cas à `ModuleShape`, puis déclarez sa primitive SVG dans `ShapeRegistry`. Ne modifiez pas la matrice QR.

## Nouveau preset

```php
$presets->register('brand', new QrStyle(...));
```

## Autre format de sortie

Conservez SVG comme renderer canonique et ajoutez un autre adaptateur raster/export, ou implémentez un nouveau renderer autour de `QrMatrix`.

## Décodeur de vérification côté serveur

Le bundle utilise `NullQrDecoder` par défaut et ne requiert aucune bibliothèque de décodage. Pour activer un contrôle réel côté serveur, implémentez `QrDecoderInterface`, décodez le SVG aux tailles fournies par `ReadabilityProfile`, puis remplacez l'alias du service. L'adaptateur doit retourner un `QrDecodeReport` et comparer le contenu décodé au contenu attendu.

```yaml
Xorgxx\NeoxQrCodeBundle\Decoder\QrDecoderInterface:
    class: App\QrCode\ServerQrDecoder
```

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
