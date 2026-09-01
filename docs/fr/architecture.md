# Architecture

```text
content / payload sécurisé
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
        |                 +--> PngRenderer optionnel (Imagick)
        v
 QrCodeGenerator
   /          \
Twig        HTTP API
Component
```

`QrCodeGenerator` est le service d'orchestration public. `QrMatrixGenerator` est un adaptateur autour de BaconQrCode. `SvgRenderer` contrôle les formes, couleurs, dégradés et placement du logo. `QrStyleValidator` effectue les vérifications de fiabilité avant le rendu.

`SecureQrPayloadService` est séparé : il produit ou valide la chaîne qui peut ensuite être encodée comme contenu QR ordinaire.
