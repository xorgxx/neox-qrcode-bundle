# Stimulus editor

`NeoxQrCodeEditor` renders controls and uses `neox_qrcode_controller.js` to request a new SVG from `/api/qrcode/svg` after changes.

The JavaScript layer **does not implement QR encoding**. Symfony stays the source of truth. This avoids maintaining two QR engines and keeps browser preview identical to server exports.

The editor supports content, preset, module/finder shape, colors, gradients, size, margin, module scale, logo and SVG/PNG download.
