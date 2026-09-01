# QR Code Studio (ready-to-use page)

`GET /qrcode/studio` (route name `xorgxx_neox_qrcode_studio`) renders a self-contained, standalone page to design a custom QR code with a fully dynamic UX: no host app CSS/JS wiring is required, the page ships its own inline styles and vanilla JavaScript.

Enable it the same way as the API routes:

```yaml
neox_qrcode:
    resource: '@NeoxQrCodeBundle/config/routes.yaml'
```

## What it does

- Live preview updated on every change, rendered by the same `/api/qrcode/svg` endpoint used by the Stimulus editor (no duplicated QR/SVG logic, see `docs/architecture.md`).
- A visual gallery of all built-in presets (`QrPresetRegistry`), each rendered server-side as a real thumbnail.
- Full controls for module/finder/alignment shapes, colors, gradients, finder effects, finder icon overlay, logo, frame shape/label, size, margin, module scale and error correction.
- Live contrast/reliability feedback via `/api/qrcode/validate` (errors, warnings, or contrast ratio).
- SVG/PNG export buttons, and a "copy Twig component" action that generates a ready-to-paste `<twig:NeoxQrCode ... />` snippet matching the current settings.
- A "random content" button to quickly preview different payloads (URL, email, Wi-Fi, phone).

## Difference with `NeoxQrCodeEditor`

`NeoxQrCodeEditor` (see `docs/component.md`) is a Twig component meant to be embedded inside a host application page; it relies on the host app's Stimulus/AssetMapper setup. The Studio page is an independent, drop-in route for quickly designing and exporting a QR code without any front-end build step — both call the exact same HTTP API and therefore the exact same rendering engine.

## Customizing

The controller (`Xorgxx\NeoxQrCodeBundle\Controller\QrCodeStudioController`) and template (`templates/studio.html.twig`) are plain Symfony/Twig; override the template path in your own bundle configuration or fork the route if you need a different layout, while keeping the same API calls.
