# Twig Components

## Display

```twig
<twig:NeoxQrCode
    content="{{ absolute_url(path('app_home')) }}"
    size="400"
    margin="4"
    moduleShape="heart"
    finderShape="rounded"
    foreground="#111111"
    background="#ffffff"
    finderColor="#D59618"
    moduleScale="0.92"
    gradientType="none"
    errorCorrection="H"
/>
```

Supported module shapes: `square`, `rounded`, `dot`, `diamond`, `heart`.
Supported finder shapes: `square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`, `dotted`, `minimal`, `inverted`.
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
    finderShape="rounded"
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

`frameShape` accepts `none`, `circle`, `rounded_square`, `heart`, `star`, `hexagon`. Non-square frames clip the QR code, so validate scannability before production use.

## Presets

```twig
<twig:NeoxQrCode content="https://example.com" preset="gold" />
```

Built-in presets (`Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry`):

- `classic` -> default square style
- `dots` -> dotted modules, rounded finders
- `rounded` -> rounded modules and finders
- `heart` -> heart-shaped modules
- `gold` -> dotted modules with a gold finder color
- `gradient` -> rounded modules with a linear foreground gradient
- `minimal` -> dotted modules with minimalist open-bracket finders
- `inverted` -> rounded modules with color-inverted finders
- `star` -> diamond modules with star-shaped gold finders
- `outline` -> square modules with a double-stroke finder outline
- `stitched` -> rounded modules with a dashed finder outline
- `floating` -> rounded modules with a drop-shadow finder effect
- `neon` -> dotted modules with a radial gradient and matching finder gradient

Register your own with `QrPresetRegistry::register()` (see `docs/extension.md`).

## Editor

```twig
<twig:NeoxQrCodeEditor content="https://example.com" />
```
