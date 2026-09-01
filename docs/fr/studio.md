# QR Code Studio (page prête à l'emploi)

`GET /qrcode/studio` (route `xorgxx_neox_qrcode_studio`) affiche une page autonome pour designer un QR code personnalisé avec une UX entièrement dynamique : aucun câblage CSS/JS de l'application hôte n'est requis, la page embarque ses propres styles inline et JavaScript vanilla.

Activez-la de la même manière que les routes API :

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

## Ce qu'elle fait

- Aperçu en direct mis à jour à chaque changement, rendu par le même endpoint `/api/qrcode/svg` que l'éditeur Stimulus (pas de logique QR/SVG dupliquée, voir `docs/architecture.md`).
- Une galerie visuelle de tous les presets intégrés (`QrPresetRegistry`), chacun rendu côté serveur comme vraie vignette.
- Contrôles complets pour les formes de modules/finders/alignement, couleurs, dégradés, effets de finder, icône de finder, logo, forme/label de cadre, taille, marge, échelle des modules et correction d'erreur.
- Retour de contraste/fiabilité en direct via `/api/qrcode/validate` (erreurs, avertissements, ou ratio de contraste).
- Boutons d'export SVG/PNG, et une action « copier le composant Twig » qui génère un snippet `<twig:NeoxQrCode ... />` prêt à coller correspondant aux réglages actuels.
- Un bouton « contenu aléatoire » pour prévisualiser rapidement différents payloads (URL, email, Wi-Fi, téléphone).

## Différence avec `NeoxQrCodeEditor`

`NeoxQrCodeEditor` (voir `docs/component.md`) est un composant Twig conçu pour être intégré dans une page d'application hôte ; il s'appuie sur la configuration Stimulus/AssetMapper de l'application. La page Studio est une route indépendante, prête à l'emploi, pour designer et exporter rapidement un QR code sans aucune étape de build front-end — les deux appellent la même API HTTP et donc le même moteur de rendu.

## Personnalisation

Le contrôleur (`Xorgxx\NeoxQrCodeBundle\Controller\QrCodeStudioController`) et le template (`templates/studio.html.twig`) sont du Symfony/Twig standard ; surchargez le chemin du template dans votre configuration de bundle ou forkez la route si vous besoin d'une disposition différente, en conservant les mêmes appels API.
