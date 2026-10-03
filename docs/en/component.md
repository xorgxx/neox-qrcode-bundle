# Twig Components

## Display

```twig
<twig:NeoxQrCode
    content="{{ absolute_url(path('app_home')) }}"
    size="400"
    margin="4"
    moduleShape="heart"
    finderFrameShape="rounded"
    finderEyeShape="star"
    foreground="#111111"
    background="#ffffff"
    finderColor="#D59618"
    moduleScale="0.92"
    gradientType="none"
    errorCorrection="H"
/>
```

Supported module shapes: `square`, `rounded`, `dot`, `diamond`, `heart`, `liquid`, `blob`, `wave`, `cross`.
Supported finder frames: `square`, `rounded`, `circle`. Eye shapes: `square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`, `octagon`, `shield`, `heart`, `flower`. `finderShape` remains available for compatibility.
Gradient types: `none`, `linear`, `radial`.

## Logo

```twig
<twig:NeoxQrCode
    content="https://example.com"
    logoHref="/images/brand.svg"
    logoScale="0.18"
    :logoBackground="true"
    errorCorrection="H"
/>
```

For safety, logo hrefs are limited to application-relative URLs and image data URIs.

## Finder icon and effects

```twig
<twig:NeoxQrCode
    content="https://example.com"
    finderFrameShape="rounded"
    finderIconHref="/images/icon.svg"
    finderIconScale="0.6"
    finderEffect="double_stroke"
    errorCorrection="H"
/>
```

`finderIconHref` overlays a small image at the center of all three finders (same URL-safety rules as `logoHref`). `finderEffect` accepts `none`, `double_stroke`, `dashed`, `shadow`, `gradient`; when `gradient` is used, `finderGradientTo` is required. See `docs/styling.md` for details.

## Frame

```twig
<twig:NeoxQrCode
    content="https://example.com"
    frameShape="circle"
    frameLabel="Scan me"
    frameLabelColor="#111111"
    errorCorrection="H"
/>
```

`frameShape` accepts `none`, `circle`, `rounded_square`, `heart`, `star`, `hexagon`, `security`. Non-square frames clip the QR code, so validate scannability before production use. The `security` frame renders a header band whose text can be customized with `frameHeader` (default `CODE SÉCURITÉ`).

## Presets

```twig
<twig:NeoxQrCode content="https://example.com" preset="gold" />
```

`preset` first looks up built-in presets, then user presets saved from the Studio/API (`UserPresetStore`) — `<twig:NeoxQrCode preset="my-custom" />` works with both.

Built-in presets (`Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry`):

- `classic` -> default square style
- `dots` -> dotted modules, rounded finders
- `rounded` -> rounded modules and finders
- `heart` -> liquid red modules inside a heart frame
- `gold` -> dotted modules with a gold finder color
- `neon` -> dotted modules with a radial gradient and matching finder gradient
- `liquid-security` -> liquid modules inside the `security` badge frame
- `liquid-heart` -> liquid blue modules inside a heart frame
- `liquid-hexagon` -> liquid modules inside a hexagon frame
- `liquid-circle` -> liquid modules inside a circle frame
- `liquid-star` -> liquid modules inside a star frame

Register your own with `QrPresetRegistry::register()` (see `docs/extension.md`).

## Bare output and transparency

```twig
{# SVG only, no surrounding markup #}
<twig:NeoxQrCode content="https://example.com" :bare="true" />

{# Transparent background (test the QR on its final surface) #}
<twig:NeoxQrCode content="https://example.com" :transparent="true" />
```

`:transparent="true"` is equivalent to `background="transparent"`; no background rect is emitted and finder/alignment cutouts become real holes.

`<twig:NeoxQrCode>` renders a static SVG even when the bundle routes are not imported; the preset selector and the Studio link are simply skipped until `@NeoxQrCodeBundle/config/routes.yaml` is imported.

## Editor

```twig
<twig:NeoxQrCodeEditor content="https://example.com" />
```
