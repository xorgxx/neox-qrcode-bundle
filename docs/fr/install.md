# Installation

```bash
composer require xorgxx/neox-qrcode-bundle
```

Prérequis : PHP 8.4+, Symfony 7.4/8.0, Twig Component. Imagick est optionnel pour l'export PNG ; Sodium est optionnel pour les payloads chiffrés.

Activez le bundle si Flex ne l'a pas fait automatiquement :

```php
Xorgxx\NeoxQrCodeBundle\NeoxQrCodeBundle::class => ['all' => true],
```

Routes API optionnelles :

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

Le contrôleur Stimulus se trouve dans `assets/controllers/neox_qrcode_controller.js`. Exposez-le via votre workflow AssetMapper/Encore habituel. Les styles optionnels sont dans `assets/styles/neox_qrcode.css`.

Pour les payloads sécurisés :

```dotenv
NEOX_QRCODE_SECRET=<au-moins-32-caractères-aléatoires>
```

Ne commitez jamais le secret de production.
