# Composants Twig

## Affichage

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

Formes de modules supportées : `square`, `rounded`, `dot`, `diamond`, `heart`.
Formes de finder supportées : `square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`, `dotted`, `minimal`, `inverted`.
Types de dégradé : `none`, `linear`, `radial`.

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

Pour des raisons de sécurité, les URLs de logo sont limitées aux URLs relatives à l'application et aux data URIs d'images.

## Icône et effets de finder

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

`finderIconHref` superpose une petite image au centre des trois finders (mêmes règles de sécurité que `logoHref`). `finderEffect` accepte `none`, `double_stroke`, `dashed`, `shadow`, `gradient` ; quand `gradient` est utilisé, `finderGradientTo` est requis. Voir `docs/styling.md` pour les détails.

## Cadre (Frame)

```twig
<twig:NeoxQrCode
    content="https://example.com"
    frameShape="circle"
    frameLabel="Scannez-moi"
    frameLabelColor="#111111"
    errorCorrection="H"
/>
```

`frameShape` accepte `none`, `circle`, `rounded_square`, `heart`, `star`, `hexagon`. Les cadres non carrés rognent le QR code, validez la scannabilité avant utilisation en production.

## Presets

```twig
<twig:NeoxQrCode content="https://example.com" preset="gold" />
```

Presets intégrés (`Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry`) :

- `classic` -> style carré par défaut
- `dots` -> modules en points, finders arrondis
- `rounded` -> modules et finders arrondis
- `heart` -> modules en forme de cœurs
- `gold` -> modules en points avec finder doré
- `gradient` -> modules arrondis avec dégradé linéaire
- `minimal` -> modules en points avec finders minimalistes
- `inverted` -> modules arrondis avec finders inversés
- `star` -> modules en diamant avec finders en étoile dorés
- `outline` -> modules carrés avec contour double du finder
- `stitched` -> modules arrondis avec contour pointillé du finder
- `floating` -> modules arrondis avec ombre portée sur le finder
- `neon` -> modules en points avec dégradé radial et finder dégradé

Enregistrez vos propres presets avec `QrPresetRegistry::register()` (voir `docs/extension.md`).

## Éditeur

```twig
<twig:NeoxQrCodeEditor content="https://example.com" />
```
