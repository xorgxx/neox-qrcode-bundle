# Documentation Neox QR Code (Français)

Neox QR Code sépare volontairement **l'encodage QR** du **rendu visuel** et des **payloads sécurisés**.

## Documentation

- [Installation](install.md)
- [Architecture](architecture.md)
- [Composants Twig](component.md)
- [Style et SVG](styling.md)
- [Registre des formes](shapes.md)
- [Éditeur Stimulus](stimulus.md)
- [QR Code Studio (page prête à l'emploi)](studio.md)
- [API HTTP](api.md)
- [Payloads QR sécurisés](security.md)
- [Étendre le package](extension.md)

## Philosophie de design

1. BaconQrCode gère la génération de matrice conforme aux standards.
2. Neox gère l'apparence SVG.
3. Twig/Stimulus ne sont que des couches de présentation et d'édition.
4. L'API et Twig appellent le même `QrCodeGenerator`.
5. La sécurité protège le **payload**, pas la matrice QR.
6. Les nouveaux renderers et stratégies de payload ne doivent pas changer l'API publique du composant.
