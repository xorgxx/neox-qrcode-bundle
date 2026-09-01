# Registre des formes

Toutes les définitions de formes SVG utilisées par le renderer QR sont centralisées dans une seule classe : `Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry`.

## Principe

Chaque forme est définie une fois comme un **tableau de données** (type + paramètres). Une seule méthode `renderShape()` génère l'élément SVG pour n'importe quelle forme à n'importe quelle position, taille et couleur — pas de blocs `match` dupliqués.

```
QrStyle (valeur d'enum)
  ↓
SvgRenderer
  ↓
ShapeRegistry::renderModule() / renderFinder() / renderAlignment()
  ↓
renderShape() — méthode unique pour toutes les formes
  ↓
Élément SVG (<rect>, <circle>, <path>)
```

## Types de formes

| Type      | Sortie SVG     | Paramètres         |
|-----------|---------------|---------------------|
| `rect`    | `<rect>`      | `radius` optionnel  |
| `circle`  | `<circle>`    | —                   |
| `diamond` | `<path>`      | —                   |
| `path`    | `<path>`      | `path` (chemin nommé) |
| `none`    | (vide)        | —                   |

## Formes supportées

### Formes de modules (enum `ModuleShape`)

| Valeur     | Élément SVG     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Carré plein                        |
| `rounded`  | `<rect rx>`     | Carré arrondi (rx = 0.35 × size)   |
| `dot`      | `<circle>`      | Cercle plein                       |
| `diamond`  | `<path>`        | Diamant à 4 points                 |
| `heart`    | `<path>`        | Cœur (chemin normalisé)            |
| `liquid`   | `<rect rx>`     | Filtre metaball fusionnant les modules adjacents |

### Formes de finder (enum `FinderShape`)

| Valeur     | Élément SVG     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Anneau carré plein                 |
| `rounded`  | `<rect rx>`     | Anneau carré arrondi (rx = 0.28)   |
| `circle`   | `<circle>`      | Cercles concentriques              |
| `diamond`  | `<path>`        | Diamants concentriques             |
| `leaf`     | `<path>`        | Feuille (chemin normalisé)         |
| `hexagon`  | `<path>`        | Hexagone (chemin normalisé)        |
| `star`     | `<path>`        | Étoile 5 branches (chemin normalisé) |
| `dotted`   | `<circle>`      | Petits points (par module)         |
| `minimal`  | `<path>`        | Crochets d'angle uniquement        |
| `inverted` | `<rect>`        | Couleurs inversées (par module)    |

### Formes d'alignement (enum `AlignmentShape`)

| Valeur     | Élément SVG     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Anneau carré plein                 |
| `rounded`  | `<rect rx>`     | Anneau carré arrondi               |
| `circle`   | `<circle>`      | Cercles concentriques              |
| `diamond`  | `<path>`        | Diamants concentriques             |
| `leaf`     | `<path>`        | Feuille                            |
| `dot`      | `<circle>`      | Petits points                      |

## Ajouter une forme personnalisée

### Option 1 : À l'exécution (surcharge)

```php
$shapeRegistry = $container->get(ShapeRegistry::class);

// Enregistrer un nouveau chemin
$shapeRegistry->setPath('myCustomShape', 'M 0.2 0.2 L 0.8 0.2 L 0.5 0.8 Z');

// Surcharger le rayon arrondi
$shapeRegistry->setCornerRadius('rounded', 0.45);
```

### Option 2 : Permanent (dans le code)

Exemple complet : ajout d'une forme de module `Cross`.

#### Étape 1 — `src/Enum/ModuleShape.php`

```php
enum ModuleShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Dot = 'dot';
    case Diamond = 'diamond';
    case Heart = 'heart';
    case Liquid = 'liquid';
    case Cross = 'cross';          // <-- nouveau
}
```

#### Étape 2 — `src/Service/ShapeRegistry.php`

Si basé sur un chemin, ajoutez les données du chemin au tableau `$paths` :

```php
private array $paths = [
    'heart'   => 'M 0.5 0.92 C ...',
    'leaf'    => 'M 0.5 0.03 C ...',
    'hexagon' => 'M 0.5 0.06 L ...',
    'star'    => 'M 0.5 0.05 L ...',
    'cross'   => 'M 0.35 0.35 L 0.65 0.35 L 0.65 0.65 L 0.35 0.65 Z',  // <-- nouveau
];
```

Ajoutez la définition au tableau `$moduleShapes` :

```php
private array $moduleShapes = [
    'square'  => ['type' => self::TYPE_RECT],
    'rounded' => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
    'dot'     => ['type' => self::TYPE_CIRCLE],
    'diamond' => ['type' => self::TYPE_DIAMOND],
    'heart'   => ['type' => self::TYPE_PATH, 'path' => 'heart'],
    'liquid'  => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
    'cross'   => ['type' => self::TYPE_PATH, 'path' => 'cross'],       // <-- nouveau
];
```

#### Étape 3 — Terminé

Aucun changement nécessaire dans `SvgRenderer`. Le renderer délègue déjà à `ShapeRegistry::renderModule()`.

#### Exposition dans l'UI

**`templates/studio.html.twig`** — ajoutez une `<option>` :

```html
<select id="neox-moduleShape">
    <option value="square">Carré</option>
    <!-- ... -->
    <option value="cross">Croix</option>
</select>
```

**`templates/components/QrCodeEditor.html.twig`** — pareil :

```html
<select data-neox-qrcode-target="moduleShape" data-action="change->neox-qrcode#refresh">
    <option value="square">Carré</option>
    <!-- ... -->
    <option value="cross">Croix</option>
</select>
```

#### Même pattern pour FinderShape et AlignmentShape

| Étape | FinderShape | AlignmentShape |
|-------|-------------|----------------|
| Enum  | `src/Enum/FinderShape.php` | `src/Enum/AlignmentShape.php` |
| Chemin| `$paths` dans `ShapeRegistry` | `$paths` dans `ShapeRegistry` |
| Rendu | `renderFinder()` dans `ShapeRegistry` | `renderAlignment()` dans `ShapeRegistry` |
| UI    | select `neox-finderShape` | select `neox-alignmentShape` |

#### Format des chemins

Tous les chemins utilisent des **coordonnées normalisées** (0.0 à 1.0). Le `ShapeRegistry` applique `translate(x, y) scale(s)` pour positionner et dimensionner la forme :

```svg
<path d="M 0.5 0.06 L 0.92 0.30 ..." transform="translate(10 20) scale(7)" fill="#000"/>
```

Un chemin défini dans un cadre 1×1 sera mis à l'échelle pour n'importe quelle taille de module, anneau de finder ou bloc d'alignement.

## Intégration

`ShapeRegistry` est autowired par Symfony et injecté dans `SvgRenderer` :

```yaml
# config/services.yaml (automatique, pas de config manuelle)
Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry: ~
Xorgxx\NeoxQrCodeBundle\Renderer\SvgRenderer:
    arguments:
        $shapes: '@Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry'
```

## Effet liquide

La forme de module `liquid` utilise un filtre metaball SVG (`feGaussianBlur` + `feColorMatrix` seuil) pour fusionner tous les modules de données adjacents — horizontalement, verticalement et diagonalement — en formes continues. Les finders et motifs d'alignement sont rendus en dehors du filtre pour rester nets.

```svg
<filter id="neoxLiquidFilter">
  <feGaussianBlur in="SourceGraphic" stdDeviation="0.4"/>
  <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -9"/>
</filter>
```
