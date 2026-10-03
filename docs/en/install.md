# Installation

```bash
composer require xorgxx/neox-qrcode-bundle
```

Requirements: PHP 8.3+, Symfony 7.4/8.0, Twig Component. Imagick is optional for PNG export; Sodium is optional for encrypted payloads; `symfony/rate-limiter` is optional for API throttling.

Enable the bundle if Flex did not do it:

```php
Xorgxx\NeoxQrCodeBundle\NeoxQrCodeBundle::class => ['all' => true],
```

Optional API routes:

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

`<twig:NeoxQrCode>` still renders a static SVG without them, but the routes are required for the live preset selector, the `/qrcode/studio` link, `NeoxQrCodeEditor`, and the HTTP API. Endpoints used by the editor are generated from route names, so a prefixed mount keeps working.

The Stimulus controller lives at `assets/controllers/neox_qrcode_controller.js`. Expose/copy it through your normal AssetMapper/Encore package asset workflow and register it under the `neox-qrcode` name (see `docs/stimulus.md`). Optional styles live at `assets/styles/neox_qrcode.css`.

For secure payloads:

```dotenv
NEOX_QRCODE_SECRET=<at-least-32-random-characters>
```

Never commit the production secret.
