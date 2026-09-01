# Payloads QR sécurisés

La sécurité s'applique au **contenu** QR, tandis que la génération de matrice et le rendu SVG restent inchangés.

## Signé + expirant

```php
$token = $secureQr->sign(
    ['ticket' => 123],
    new \DateTimeImmutable('+15 minutes')
);
```

Placez soit le token lui-même, soit de préférence une URL HTTPS normale contenant ce token dans le QR. N'importe quel téléphone peut scanner l'URL ; Symfony valide le token côté serveur.

```php
$payload = $secureQr->verify($token);
```

## À usage unique

```php
$token = $secureQr->sign(
    ['ticket' => 123],
    new \DateTimeImmutable('+15 minutes'),
    singleUse: true,
);

$payload = $secureQr->verify($token, consume: true);
```

L'implémentation par défaut utilise `CacheSingleUseTokenStore` basé sur le cache Symfony `cache.app`. Pour un contrôle d'accès multi-nœuds haute assurance, implémentez `SingleUseTokenStoreInterface` avec un store de tokens atomique en base de données/Redis et câblez-le dans `services.yaml` :

```yaml
Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface:
    class: App\Security\RedisTokenStore
```

## Chiffré

Avec ext-sodium :

```php
$token = $secureQr->encrypt(['private' => 'value'], new \DateTimeImmutable('+5 minutes'));
$data = $secureQr->decrypt($token);
```

Les payloads propriétaires chiffrés nécessitent un logiciel qui comprend le format. Si un scan universel par téléphone est souhaité, encodez plutôt une URL HTTPS avec un token opaque/signé.

## Secret

`NEOX_QRCODE_SECRET` doit être aléatoire, privé, d'au moins 32 caractères et rotulé selon votre politique de sécurité applicative.
