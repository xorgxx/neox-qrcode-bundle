# Installation

```bash
composer require xorgxx/neox-qrcode-bundle
```

Requirements: PHP 8.4+, Symfony 7.4/8.0, Twig Component. Imagick is optional for PNG export; Sodium is optional for encrypted payloads.

Enable the bundle if Flex did not do it:

```php
Xorgxx\NeoxQrCodeBundle\NeoxQrCodeBundle::class => ['all' => true],
```

Optional API routes:

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

The Stimulus controller lives at `assets/controllers/neox_qrcode_controller.js`. Expose/copy it through your normal AssetMapper/Encore package asset workflow. Optional styles live at `assets/styles/neox_qrcode.css`.

For secure payloads:

```dotenv
NEOX_QRCODE_SECRET=<at-least-32-random-characters>
```

Never commit the production secret.
