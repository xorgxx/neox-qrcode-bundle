# Style et SVG

La matrice QR n'est jamais modérée cosmétiquement. Seuls les modules actifs sont rendus avec des primitives SVG alternatives.

## Formes de modules

- `square` -> `<rect>`
- `rounded` -> `<rect>` arrondi
- `dot` -> `<circle>`
- `diamond` -> `<path>` SVG
- `heart` -> `<path>` SVG normalisé
- `liquid` -> les modules de données adjacents (horizontal, vertical, diagonal) sont fusionnés via un filtre metaball SVG
- `blob` -> modules ronds reliés aux voisins sombres orthogonaux par de larges ponts arrondis
- `wave` -> modules ronds compacts reliés par des ponts quadratiques ondulés et alternés
- `cross` -> modules en croix reliés par des ponts fins et arrondis

Le filtre liquide est limité aux modules de données ordinaires. Les cadres des finders et les motifs d'alignement sont rendus ensuite, sans filtre, afin de conserver une géométrie fonctionnelle nette. Le score de validation applique une pénalité de cinq points et émet un avertissement ; testez chaque taille d'affichage ou d'impression prévue avec de vrais scanners.

## Cadre et œil des finders

Les finders (les trois repères d'angle) sont rendus indépendamment des modules de données ordinaires. Leur cadre fonctionnel est limité à `square`, `rounded` ou `circle` afin de conserver une structure détectable par les scanners.

- `square` -> `<rect>`
- `rounded` -> `<rect>` arrondi
- `circle` -> `<circle>`

Un `finderColor` séparé peut être défini pour différencier les finders des modules de données.

Le cadre et sa découpe sont rendus comme deux formes concentriques unifiées (7x7 et 5x5). Les anciennes valeurs décoratives de `finderShape` restent acceptées : elles utilisent désormais un cadre carré canonique et appliquent leur forme uniquement à l'œil.

### Forme de l'œil du finder

`finderEyeShape` contrôle indépendamment le bloc 3x3 central (« œil »), avec `square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`, `octagon`, `shield`, `heart` ou `flower` (fleur simple à quatre pétales). `finderFrameShape` contrôle le cadre sûr.

L'œil `star` utilise un chemin spécifique plus massif que l'étoile décorative générale. Ses creux sont moins profonds afin de conserver suffisamment de surface sombre.

Les yeux décoratifs sont rendus sur 3,2 modules, contre 3 modules pour les formes classiques. Ils restent centrés et ne réduisent la séparation claire que de 0,1 module de chaque côté.

`finderEyeScale` permet d'ajuster cette taille entre `0.85` et `1.08`. La valeur par défaut est `1.0`; augmenter la valeur réduit la séparation claire et diminue légèrement le score estimé.

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
