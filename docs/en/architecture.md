# Architecture

```text
content / secure payload
        |
        v
QrMatrixGenerator -> BaconQrCode Encoder
        |
        v
     QrMatrix
        |
        v
  SvgRenderer --------> SVG
        |                 |
        |                 +--> optional PngRenderer (Imagick)
        v
 QrCodeGenerator
   /          \
Twig        HTTP API
Component
```

`QrCodeGenerator` is the public orchestration service. `QrMatrixGenerator` is an adapter around BaconQrCode. `SvgRenderer` controls shapes, colors, gradients and logo placement. `QrStyleValidator` performs reliability checks before rendering.

`SecureQrPayloadService` is separate: it produces or validates the string that can then be encoded as ordinary QR content.
