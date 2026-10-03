# Composants Twig

## Affichage

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

Formes de modules supportées : `square`, `rounded`, `dot`, `diamond`, `heart`, `liquid`, `blob`, `wave`, `cross`.
Cadres de finder supportés : `square`, `rounded`, `circle`. Formes d'œil : `square`, `rounded`, `circle`, `diamond`, `leaf`, `hexagon`, `star`, `octagon`, `shield`, `heart`, `flower`. `finderShape` reste disponible pour la compatibilité.
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
    finderFrameShape="rounded"
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

`frameShape` accepte `none`, `circle`, `rounded_square`, `heart`, `star`, `hexagon`, `security`. Les cadres non carrés rognent le QR code, validez la scannabilité avant utilisation en production. Le cadre `security` affiche un bandeau d'en-tête dont le texte se personnalise avec `frameHeader` (défaut `CODE SÉCURITÉ`).

## Presets

```twig
<twig:NeoxQrCode content="https://example.com" preset="gold" />
```

`preset` cherche d'abord parmi les presets intégrés, puis parmi les presets utilisateur enregistrés depuis le Studio/l'API (`UserPresetStore`) — `<twig:NeoxQrCode preset="mon-custom" />` fonctionne dans les deux cas.

Presets intégrés (`Xorgxx\NeoxQrCodeBundle\Service\QrPresetRegistry`) :

- `classic` -> style carré par défaut
- `dots` -> modules en points, finders arrondis
- `rounded` -> modules et finders arrondis
- `heart` -> modules liquides rouges dans un cadre cœur
- `gold` -> modules en points avec finder doré
- `neon` -> modules en points avec dégradé radial et finder dégradé
- `liquid-security` -> modules liquides dans le cadre badge `security`
- `liquid-heart` -> modules liquides bleus dans un cadre cœur
- `liquid-hexagon` -> modules liquides dans un cadre hexagone
- `liquid-circle` -> modules liquides dans un cadre cercle
- `liquid-star` -> modules liquides dans un cadre étoile

Enregistrez vos propres presets avec `QrPresetRegistry::register()` (voir `docs/extension.md`).

## Sortie bare et transparence

```twig
{# SVG seul, sans markup autour #}
<twig:NeoxQrCode content="https://example.com" :bare="true" />

{# Fond transparent (testez le QR sur sa surface finale) #}
<twig:NeoxQrCode content="https://example.com" :transparent="true" />
```

`:transparent="true"` équivaut à `background="transparent"` ; aucun rect de fond n'est émis et les découpes des repères deviennent de vrais trous.

`<twig:NeoxQrCode>` rend un SVG statique même si les routes du bundle ne sont pas importées ; le sélecteur de presets et le lien Studio sont simplement ignorés tant que `@NeoxQrCodeBundle/config/routes.yaml` n'est pas importé.

## Éditeur

```twig
<twig:NeoxQrCodeEditor content="https://example.com" />
```
