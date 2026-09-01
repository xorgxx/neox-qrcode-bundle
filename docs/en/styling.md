# Styling and SVG

The QR matrix is never cosmetically altered. Only active modules are rendered with alternate SVG primitives.

## Module shapes

- `square` -> `<rect>`
- `rounded` -> rounded `<rect>`
- `dot` -> `<circle>`
- `diamond` -> SVG `<path>`
- `heart` -> normalized SVG `<path>`
- `liquid` -> all touching data modules (horizontal, vertical, diagonal) are merged into continuous blob shapes via an SVG metaball filter (Gaussian blur + alpha threshold), creating a true liquid/fluid appearance

## Finder shapes

Finders (the three corner squares) are rendered independently from ordinary data modules.

- `square` -> `<rect>`
- `rounded` -> rounded `<rect>`
- `circle` -> `<circle>`
- `diamond` -> SVG `<path>`
- `leaf` -> SVG `<path>`
- `hexagon` -> SVG `<path>`
- `star` -> SVG `<path>`
- `dotted` -> small `<circle>` per module (dotted ring look)
- `minimal` -> open corner brackets only (no ring/square fill)
- `inverted` -> colors of the finder pattern are swapped (dark <-> background)

A separate `finderColor` can be set to differentiate finders from data modules.

`square`, `rounded`, `circle` and `diamond` are rendered as **three unified concentric shapes** (outer 7x7 ring, 5x5 background cutout, 3x3 eye) instead of a mosaic of individually shaped modules. This guarantees a genuinely continuous, solid outline for `circle` and `diamond` (previously a ring of visually disconnected dots/points).

### Finder eye shape

`finderEyeShape` independently controls the innermost 3x3 block ("eye") of the finder, using the same `FinderShape` enum as the outer ring (`square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`). When omitted, it defaults to a shape matching the outer ring. This lets you mix, for example, a solid square outer ring with a round eye, or a circle ring with a star eye.

`finderCenterShape` (legacy) also controls the eye but uses `ModuleShape` values. If both are set, `finderEyeShape` takes priority.

### Finder icon overlay

Set `finderIconHref` (application-relative URL or image data URI) to overlay a small image at the center of all three finders, in addition to the central logo. Use `finderIconScale` (0.2-0.85) to control its size relative to the inner 3x3 finder square. As with the central logo, prefer error correction `H` and test scans when combining this with other decorations.

### Finder effects

`finderEffect` adds a decorative treatment around/behind the finder, independent of `finderShape`:

- `none` -> default, no extra effect
- `double_stroke` -> two concentric outline rings drawn around the finder
- `dashed` -> a dashed outline ring drawn around the finder
- `shadow` -> a simple offset drop-shadow silhouette drawn behind the finder
- `gradient` -> the finder fill uses a radial gradient from `finderColor` to `finderGradientTo` (required when this effect is selected)

## Alignment shapes

Alignment patterns (the smaller reference squares in versions >= 2) are rendered independently with their own shape and color.

- `square` -> `<rect>`
- `rounded` -> rounded `<rect>`
- `circle` -> `<circle>`
- `diamond` -> SVG `<path>`
- `leaf` -> SVG `<path>`
- `dot` -> smaller `<circle>`

A separate `alignmentColor` can be set. If omitted, it falls back to `finderColor`, then `foreground`.

Gradients are generated in `<defs>` and used as the module fill.

## Frame shapes

A `QrFrameStyle` can wrap the whole QR code in a decorative outer shape via `FrameRenderer` (used automatically by `QrCodeGenerator::generate()` when a frame is passed, and exposed on the `NeoxQrCode` Twig component via `frameShape`/`frameLabel`/`frameLabelColor`).

- `none` -> no frame (default, fully backward compatible)
- `circle` -> clips the QR into a circle
- `rounded_square` -> clips the QR into a rounded square
- `heart` -> clips the QR into a heart shape
- `star` -> clips the QR into a star shape
- `hexagon` -> clips the QR into a hexagon shape

An optional `label` is rendered below the shape. Clipping a QR code into a non-square shape can crop finder/data modules near the edges: always validate scannability with real devices before using a frame in production, and prefer higher error correction (`H`) and a generous `margin`.

## Reliability rules

Keep a quiet zone of about 4 modules, strong foreground/background contrast, error correction `H` for central logos, and avoid oversized logos. Decorative QR codes must be tested with multiple cameras before production use.
