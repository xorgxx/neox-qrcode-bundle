# Éditeur Stimulus

`NeoxQrCodeEditor` affiche les contrôles et utilise `neox_qrcode_controller.js` pour demander un nouveau SVG à `/api/qrcode/svg` après chaque modification.

La couche JavaScript **n'implémente pas l'encodage QR**. Symfony reste la source de vérité. Cela évite de maintenir deux moteurs QR et garantit que l'aperçu navigateur est identique aux exports serveur.

L'éditeur supporte le contenu, les presets, les formes de modules/finders, les couleurs, les dégradés, la taille, la marge, l'échelle des modules, le logo et le téléchargement SVG/PNG.
