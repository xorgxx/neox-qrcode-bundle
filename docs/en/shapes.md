# Shape Registry

All SVG shape definitions used by the QR code renderer are centralized in a single class: `Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry`.

## Principle

Every shape is defined once as a **data array** (type + parameters). A single `renderShape()` method generates the SVG element for any shape at any position, size and color — no duplicated `match` blocks.

```
QrStyle (enum value)
  ↓
SvgRenderer
  ↓
ShapeRegistry::renderModule() / renderFinder() / renderAlignment()
  ↓
renderShape() — single method for all shapes
  ↓
SVG element (<rect>, <circle>, <path>)
```

## Shape types

| Type      | SVG output    | Parameters          |
|-----------|---------------|---------------------|
| `rect`    | `<rect>`      | optional `radius`   |
| `circle`  | `<circle>`    | —                   |
| `diamond` | `<path>`      | —                   |
| `path`    | `<path>`      | `path` (named path) |
| `none`    | (empty)       | —                   |

## Supported shapes

### Module shapes (`ModuleShape` enum)

| Value      | SVG element     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Solid square                       |
| `rounded`  | `<rect rx>`     | Rounded square (rx = 0.35 × size)  |
| `dot`      | `<circle>`      | Full circle                        |
| `diamond`  | `<path>`        | 4-point diamond                    |
| `heart`    | `<path>`        | Heart shape (normalized path)      |
| `liquid`   | `<rect rx>`     | Metaball filter merges touching modules into continuous blobs |

### Finder shapes (`FinderShape` enum)

| Value      | SVG element     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Solid square ring                  |
| `rounded`  | `<rect rx>`     | Rounded square ring (rx = 0.28)    |
| `circle`   | `<circle>`      | Concentric circles                 |
| `diamond`  | `<path>`        | Concentric diamonds                |
| `leaf`     | `<path>`        | Leaf shape (normalized path)       |
| `hexagon`  | `<path>`        | Hexagon (normalized path)          |
| `star`     | `<path>`        | 5-point star (normalized path)     |
| `dotted`   | `<circle>`      | Small dots (per-module)            |
| `minimal`  | `<path>`        | Corner brackets only               |
| `inverted` | `<rect>`        | Inverted colors (per-module)       |

### Alignment shapes (`AlignmentShape` enum)

| Value      | SVG element     | Description                        |
|------------|-----------------|------------------------------------|
| `square`   | `<rect>`        | Solid square ring                  |
| `rounded`  | `<rect rx>`     | Rounded square ring                |
| `circle`   | `<circle>`      | Concentric circles                 |
| `diamond`  | `<path>`        | Concentric diamonds                |
| `leaf`     | `<path>`        | Leaf shape                         |
| `dot`      | `<circle>`      | Small dots                         |

## Adding a custom shape

### Option 1: At runtime (override)

```php
$shapeRegistry = $container->get(ShapeRegistry::class);

// Register a new path-based shape
$shapeRegistry->setPath('myCustomShape', 'M 0.2 0.2 L 0.8 0.2 L 0.5 0.8 Z');

// Override the rounded corner radius
$shapeRegistry->setCornerRadius('rounded', 0.45);
```

### Option 2: Permanently (in code)

Complete example: adding a `Cross` module shape.

#### Step 1 — `src/Enum/ModuleShape.php`

Add the new case to the enum:

```php
enum ModuleShape: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Dot = 'dot';
    case Diamond = 'diamond';
    case Heart = 'heart';
    case Liquid = 'liquid';
    case Cross = 'cross';          // <-- new
}
```

#### Step 2 — `src/Service/ShapeRegistry.php`

If path-based, add the path data to the `$paths` array:

```php
private array $paths = [
    'heart'   => 'M 0.5 0.92 C ...',
    'leaf'    => 'M 0.5 0.03 C ...',
    'hexagon' => 'M 0.5 0.06 L ...',
    'star'    => 'M 0.5 0.05 L ...',
    'cross'   => 'M 0.35 0.35 L 0.65 0.35 L 0.65 0.65 L 0.35 0.65 Z',  // <-- new
];
```

Add the shape definition to the `$moduleShapes` array:

```php
private array $moduleShapes = [
    'square'  => ['type' => self::TYPE_RECT],
    'rounded' => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
    'dot'     => ['type' => self::TYPE_CIRCLE],
    'diamond' => ['type' => self::TYPE_DIAMOND],
    'heart'   => ['type' => self::TYPE_PATH, 'path' => 'heart'],
    'liquid'  => ['type' => self::TYPE_RECT, 'radius' => 'rounded'],
    'cross'   => ['type' => self::TYPE_PATH, 'path' => 'cross'],       // <-- new
];
```

#### Step 3 — Done

No change needed in `SvgRenderer`. The renderer already delegates to `ShapeRegistry::renderModule()`.

#### Exposing in the UI

To make the new shape selectable in the Studio page and Twig editor:

**`templates/studio.html.twig`** — add an `<option>`:

```html
<select id="neox-moduleShape">
    <option value="square">Square</option>
    <!-- ... -->
    <option value="cross">Cross</option>
</select>
```

**`templates/components/QrCodeEditor.html.twig`** — same:

```html
<select data-neox-qrcode-target="moduleShape" data-action="change->neox-qrcode#refresh">
    <option value="square">Square</option>
    <!-- ... -->
    <option value="cross">Cross</option>
</select>
```

#### Same pattern for FinderShape and AlignmentShape

| Step | FinderShape | AlignmentShape |
|------|-------------|----------------|
| Enum | `src/Enum/FinderShape.php` | `src/Enum/AlignmentShape.php` |
| Path | `$paths` in `ShapeRegistry` | `$paths` in `ShapeRegistry` |
| Render | `renderFinder()` in `ShapeRegistry` | `renderAlignment()` in `ShapeRegistry` |
| UI | `neox-finderShape` select | `neox-alignmentShape` select |

#### Path format

All paths use **normalized coordinates** (0.0 to 1.0). The `ShapeRegistry` applies `translate(x, y) scale(s)` to position and size the shape:

```svg
<path d="M 0.5 0.06 L 0.92 0.30 ..." transform="translate(10 20) scale(7)" fill="#000"/>
```

This means a path defined in a 1×1 box will be scaled to any module size, finder ring size, or alignment block size.

## Integration

`ShapeRegistry` is autowired by Symfony and injected into `SvgRenderer`:

```yaml
# config/services.yaml (automatic, no manual config needed)
Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry: ~
Xorgxx\NeoxQrCodeBundle\Renderer\SvgRenderer:
    arguments:
        $shapes: '@Xorgxx\NeoxQrCodeBundle\Service\ShapeRegistry'
```

## Liquid effect

The `liquid` module shape uses an SVG metaball filter (`feGaussianBlur` + `feColorMatrix` threshold) to merge all touching data modules — horizontally, vertically, and diagonally — into continuous blob shapes. Finder and alignment patterns are rendered outside the filter to remain crisp.

```svg
<filter id="neoxLiquidFilter">
  <feGaussianBlur in="SourceGraphic" stdDeviation="0.4"/>
  <feColorMatrix type="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 22 -9"/>
</filter>
```
