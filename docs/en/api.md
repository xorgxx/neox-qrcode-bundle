# HTTP API

Import `@NeoxQrCodeBundle/config/routes.yaml` to enable these endpoints.

## POST `/api/qrcode/svg`
Returns `image/svg+xml`.

## POST `/api/qrcode/png`
Returns `image/png`; requires Imagick.

## POST `/api/qrcode/matrix`
Returns the logical QR matrix as JSON.

## POST `/api/qrcode/validate`
Returns style reliability diagnostics.

## GET `/api/qrcode/presets`
Returns built-in preset names.

Example payload:

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
  "frameLabel": "Scan me",
  "frameLabelColor": "#111111",
  "errorCorrection": "H"
}
```

See `docs/styling.md` for all `finderShape`, `finderEffect`, and `frameShape` values.

For a public API, add your own authentication/rate limiter/firewall rules. The package intentionally does not publish an unauthenticated endpoint that creates signed security tokens.

## Rate limiting

The POST endpoints use the `#[RateLimiter('xorgxx_neox_qrcode_api')]` attribute. To enable it, install `symfony/rate-limiter` and import the bundle's config:

```yaml
# config/packages/neox_qrcode.yaml
imports:
    - { resource: '@NeoxQrCodeBundle/config/rate_limiter.yaml' }
```

The default policy allows 60 requests per minute per client. Adjust the limits in your own config as needed.
