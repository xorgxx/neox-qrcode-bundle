# Installation

```bash
composer require xorgxx/neox-qrcode-bundle
```

Prérequis : PHP 8.3+, Symfony 7.4/8.0, Twig Component. Imagick est optionnel pour l'export PNG ; Sodium est optionnel pour les payloads chiffrés ; `symfony/rate-limiter` est optionnel pour limiter le débit de l'API.

Activez le bundle si Flex ne l'a pas fait automatiquement :

```php
Xorgxx\NeoxQrCodeBundle\NeoxQrCodeBundle::class => ['all' => true],
```

Routes API optionnelles :

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

`<twig:NeoxQrCode>` continue de rendre un SVG statique sans elles, mais ces routes sont requises pour le sélecteur de presets en direct, le lien `/qrcode/studio`, `NeoxQrCodeEditor` et l'API HTTP. Les endpoints utilisés par l'éditeur sont générés à partir des noms de routes, donc un montage sous préfixe fonctionne.

Le contrôleur Stimulus se trouve dans `assets/controllers/neox_qrcode_controller.js`. Exposez-le via votre workflow AssetMapper/Encore habituel et enregistrez-le sous le nom `neox-qrcode` (voir `docs/stimulus.md`). Les styles optionnels sont dans `assets/styles/neox_qrcode.css`.

Pour les payloads sécurisés :

```dotenv
NEOX_QRCODE_SECRET=<au-moins-32-caractères-aléatoires>
```

Ne commitez jamais le secret de production.
