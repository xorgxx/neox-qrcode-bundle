# Neox QR Code documentation

Neox QR Code intentionally separates **QR encoding** from **visual rendering** and from **secure payloads**.

## Language / Langue

- [English documentation](en/index.md)
- [Documentation française](fr/index.md)

## Design philosophy

1. BaconQrCode owns standards-compliant matrix generation.
2. Neox owns the SVG appearance.
3. Twig/Stimulus are presentation and editing layers only.
4. API and Twig call the same `QrCodeGenerator`.
5. Security protects the **payload**, not the QR matrix.
6. New renderers and payload strategies should not change the public component API.
