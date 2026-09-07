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

La réponse contient `readabilityScore` (0 à 100), `readabilityDetails` et `estimated: true`. Les détails exposent notamment marge, échelle et forme des modules, œil, correction d'erreur, logo et cadre. Ce score ne garantit pas qu'un appareil donné décodera le QR.

`moduleShape` accepte `square`, `rounded`, `dot`, `diamond`, `heart`, `liquid`, `blob`, `wave` ou `cross`. Ces formes connectées utilisent le renderer SVG canonique tout en isolant les motifs finder et d'alignement. `liquid` emploie un filtre metaball ; `blob`, `wave` et `cross` ajoutent des ponts explicites uniquement entre cellules de données sombres orthogonalement adjacentes. Leurs pénalités estimées sont respectivement de 5, 4, 7 et 8 points, avec un avertissement invitant à tester le scan.

`testProfile` accepte `compact` (96/128/256), `balanced` (128/256/512) ou `print` (256/512/1024). `serverDecode` indique si un décodeur serveur optionnel est disponible et quelles tailles ont réussi.

## GET `/api/qrcode/presets`
Retourne les noms des presets intégrés.

Exemple de payload :

```json
{
  "content": "https://example.com",
  "size": 500,
  "moduleShape": "liquid",
  "finderFrameShape": "rounded",
  "finderEyeShape": "star",
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

Voir `docs/styling.md` pour les valeurs de `finderFrameShape`, `finderEyeShape`, `finderEffect` et `frameShape`.

Pour une API publique, ajoutez votre propre authentification/limiteur de débit/règles de firewall. Le package ne publie intentionnellement pas d'endpoint non authentifié qui crée des tokens de sécurité signés.

## Limiteur de débit

Les endpoints POST utilisent l'attribut `#[RateLimiter('xorgxx_neox_qrcode_api')]`. Pour l'activer, installez `symfony/rate-limiter` et importez la config du bundle :

```yaml
# config/packages/neox_qrcode.yaml
imports:
    - { resource: '@NeoxQrCodeBundle/config/rate_limiter.yaml' }
```

La politique par défaut autorise 60 requêtes par minute par client. Ajustez les limites dans votre propre config selon vos besoins.
