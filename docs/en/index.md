# Neox QR Code documentation (English)

Neox QR Code intentionally separates **QR encoding** from **visual rendering** and from **secure payloads**.

## Documentation

- [Installation](install.md)
- [Architecture](architecture.md)
- [Twig Components](component.md)
- [Styling and SVG](styling.md)
- [Shape Registry](shapes.md)
- [Stimulus editor](stimulus.md)
- [QR Code Studio (ready-to-use page)](studio.md)
- [HTTP API](api.md)
- [Secure QR payloads](security.md)
- [Extending the package](extension.md)

## Design philosophy

1. BaconQrCode owns standards-compliant matrix generation.
2. Neox owns the SVG appearance.
3. Twig/Stimulus are presentation and editing layers only.
4. API and Twig call the same `QrCodeGenerator`.
5. Security protects the **payload**, not the QR matrix.
6. New renderers and payload strategies should not change the public component API.
