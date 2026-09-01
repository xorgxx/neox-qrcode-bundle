# Secure QR payloads

Security applies to the QR **content**, while matrix generation and SVG rendering remain unchanged.

## Signed + expiring

```php
$token = $secureQr->sign(
    ['ticket' => 123],
    new \DateTimeImmutable('+15 minutes')
);
```

Place either the token itself or, preferably, a normal HTTPS URL containing that token into the QR. Any phone can scan the URL; Symfony validates the token server-side.

```php
$payload = $secureQr->verify($token);
```

## Single-use

```php
$token = $secureQr->sign(
    ['ticket' => 123],
    new \DateTimeImmutable('+15 minutes'),
    singleUse: true,
);

$payload = $secureQr->verify($token, consume: true);
```

The default implementation uses `CacheSingleUseTokenStore` backed by Symfony `cache.app`. For high-assurance multi-node access control, implement `SingleUseTokenStoreInterface` with an atomic database/Redis token store and wire it in `services.yaml`:

```yaml
Xorgxx\NeoxQrCodeBundle\Security\SingleUseTokenStoreInterface:
    class: App\Security\RedisTokenStore
```

## Encrypted

With ext-sodium:

```php
$token = $secureQr->encrypt(['private' => 'value'], new \DateTimeImmutable('+5 minutes'));
$data = $secureQr->decrypt($token);
```

Encrypted proprietary payloads require software that understands the format. If universal phone scanning is desired, encode an HTTPS URL with an opaque/signed token instead.

## Secret

`NEOX_QRCODE_SECRET` must be random, private, at least 32 characters and rotated according to your application security policy.
