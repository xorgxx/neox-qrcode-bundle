# API HTTP

Importez `@NeoxQrCodeBundle/config/routes.yaml` pour activer ces endpoints.

## POST `/api/qrcode/svg`
Retourne `image/svg+xml`.

## POST `/api/qrcode/png`
Retourne `image/png` ; nécessite Imagick.

## POST `/api/qrcode/matrix`
Retourne la matrice QR logique en JSON.

## POST `/api/qrcode/validate`
Retourne les diagnostics de fiabilité de style.

## GET `/api/qrcode/presets`
Retourne les noms des presets intégrés.

Exemple de payload :

```json
{
  "content": "https://example.com",
  "size": 500,
  "moduleShape": "dot",
  "finderShape": "rounded",
  "foreground": "#111111",
  "background": "#ffffff",
  "gradientType": "linear",
  "gradientTo": "#D59618",
  "logoHref": "/images/logo.svg",
  "finderIconHref": "/images/icon.svg",
  "finderIconScale": 0.6,
  "finderEffect": "double_stroke",
  "finderGradientTo": "#D59618",
  "finderCenterShape": "dot",
  "finderEyeShape": "circle",
  "frameShape": "circle",
  "frameLabel": "Scannez-moi",
  "frameLabelColor": "#111111",
  "errorCorrection": "H"
}
```

Voir `docs/styling.md` pour toutes les valeurs de `finderShape`, `finderEffect` et `frameShape`.

Pour une API publique, ajoutez votre propre authentification/limiteur de débit/règles de firewall. Le package ne publie intentionnellement pas d'endpoint non authentifié qui crée des tokens de sécurité signés.

## Limiteur de débit

Les endpoints POST utilisent l'attribut `#[RateLimiter('xorgxx_neox_qrcode_api')]`. Pour l'activer, installez `symfony/rate-limiter` et importez la config du bundle :

```yaml
# config/packages/neox_qrcode.yaml
imports:
    - { resource: '@NeoxQrCodeBundle/config/rate_limiter.yaml' }
```

La politique par défaut autorise 60 requêtes par minute par client. Ajustez les limites dans votre propre config selon vos besoins.
