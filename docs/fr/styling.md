# Style et SVG

La matrice QR n'est jamais modérée cosmétiquement. Seuls les modules actifs sont rendus avec des primitives SVG alternatives.

## Formes de modules

- `square` -> `<rect>`
- `rounded` -> `<rect>` arrondi
- `dot` -> `<circle>`
- `diamond` -> `<path>` SVG
- `heart` -> `<path>` SVG normalisé
- `liquid` -> tous les modules de données adjacents (horizontal, vertical, diagonal) sont fusionnés en formes continues via un filtre metaball SVG (flou gaussien + seuil alpha), créant une apparence liquide/fluide

## Formes de finder

Les finders (les trois carrés d'angle) sont rendus indépendamment des modules de données ordinaires.

- `square` -> `<rect>`
- `rounded` -> `<rect>` arrondi
- `circle` -> `<circle>`
- `diamond` -> `<path>` SVG
- `leaf` -> `<path>` SVG
- `hexagon` -> `<path>` SVG
- `star` -> `<path>` SVG
- `dotted` -> petits `<circle>` par module (aspect anneau pointillé)
- `minimal` -> crochets d'angle uniquement (pas de remplissage anneau/carré)
- `inverted` -> les couleurs du finder sont inversées (sombre <-> fond)

Un `finderColor` séparé peut être défini pour différencier les finders des modules de données.

`square`, `rounded`, `circle` et `diamond` sont rendus comme **trois formes concentriques unifiées** (anneau extérieur 7x7, découpe 5x5, œil 3x3) au lieu d'une mosaïque de modules individuels. Cela garantit un contour véritablement continu pour `circle` et `diamond`.

### Forme de l'œil du finder

`finderEyeShape` contrôle indépendamment le bloc 3x3 central (« œil ») du finder, en utilisant la même enum `FinderShape` que l'anneau extérieur. Quand omis, il utilise par défaut une forme correspondant à l'anneau extérieur. Cela permet de mélanger, par exemple, un anneau carré avec un œil rond.

`finderCenterShape` (legacy) contrôle aussi l'œil mais utilise les valeurs de `ModuleShape`. Si les deux sont définis, `finderEyeShape` a priorité.

### Superposition d'icône sur le finder

Définissez `finderIconHref` (URL relative ou data URI) pour superposer une petite image au centre des trois finders. Utilisez `finderIconScale` (0.2-0.85) pour contrôler sa taille. Comme pour le logo central, préférez la correction d'erreur `H` et testez les scans.

### Effets de finder

`finderEffect` ajoute un traitement décoratif autour du finder :

- `none` -> par défaut, aucun effet
- `double_stroke` -> deux anneaux de contour concentriques
- `dashed` -> anneau de contour en pointillé
- `shadow` -> ombre portée décalée derrière le finder
- `gradient` -> le remplissage du finder utilise un dégradé radial de `finderColor` vers `finderGradientTo` (requis)

## Formes d'alignement

Les motifs d'alignement (les petits carrés de référence dans les versions >= 2) sont rendus indépendamment avec leur propre forme et couleur.

- `square` -> `<rect>`
- `rounded` -> `<rect>` arrondi
- `circle` -> `<circle>`
- `diamond` -> `<path>` SVG
- `leaf` -> `<path>` SVG
- `dot` -> `<circle>` plus petit

Un `alignmentColor` séparé peut être défini. Si omis, il utilise `finderColor`, puis `foreground`.

Les dégradés sont générés dans `<defs>` et utilisés comme remplissage des modules.

## Formes de cadre

Un `QrFrameStyle` peut envelopper le QR code dans une forme extérieure décorative via `FrameRenderer`.

- `none` -> pas de cadre (par défaut)
- `circle` -> rogne le QR en cercle
- `rounded_square` -> rogne le QR en carré arrondi
- `heart` -> rogne le QR en cœur
- `star` -> rogne le QR en étoile
- `hexagon` -> rogne le QR en hexagone

Un `label` optionnel est rendu sous la forme. Rogner un QR dans une forme non carrée peut couper des modules près des bords : validez toujours la scannabilité avec de vrais appareils avant utilisation en production, et préférez la correction d'erreur `H` avec une `margin` généreuse.

## Règles de fiabilité

Conservez une zone calme d'environ 4 modules, un contraste fort premier plan/arrière-plan, la correction d'erreur `H` pour les logos centraux, et évitez les logos surdimensionnés. Les QR codes décoratifs doivent être testés avec plusieurs appareils photo avant utilisation en production.
