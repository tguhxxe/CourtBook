# Home refresh assets

Created for branch `design/home-blue-refresh`. Generation used the built-in `image_gen` tool, not the API/CLI fallback. The supplied presentation collages informed the visual direction; no part of either reference image is embedded in the website.

## Saved project assets

- `public/images/home/blue-tennis-hero.webp` — 1942 × 809, primary hero.
- `public/images/home/blue-tennis-court.webp` — 1536 × 1024, generic tennis illustration reused for tennis courts.
- `public/images/home/mini-soccer.webp` — 1536 × 1024, generic mini soccer illustration.

PNG originals remain in the generator's output directory. The project copies are WebP encodings for web delivery. The UI explicitly identifies them as illustrations, not actual venue photographs. No fabricated ratings, real venue photography claims or availability status are added.

## Final generation prompts

### Hero

Use case: photorealistic-natural. Asset type: wide website hero photograph for an Indonesian tennis and mini soccer booking website. Create a premium editorial sports photograph, very wide landscape 2.4:1. A sunlit cobalt blue hard tennis court, crisp white court boundary lines running diagonally toward the back, black tennis net softly visible in the upper right background, one lime yellow tennis ball prominent in the RIGHT third foreground with natural fuzzy felt detail and a sharp afternoon shadow. Camera low near the playing surface. Keep the LEFT 55 percent as calm uninterrupted textured blue court, no objects there, to allow a white text overlay to be added later in HTML. No people, no buildings of a particular actual venue, no logos, no lettering, no watermark, no text. Color: rich medium cobalt blue, white markings, small lime ball. Beautiful tactile court texture, natural sun, believable lens and lighting, high-end sports magazine cover photography, disciplined uncluttered composition. Do not make a mockup or UI, only the photograph.

### Tennis card

Use case: photorealistic-natural. Asset type: illustrative fictional tennis court image for a website booking card. A beautifully maintained empty cobalt blue hard tennis court with crisp white doubles and service lines, low black net across its center, blue runoffs, dark navy chain-link perimeter and leafy trees behind. Landscape 3:2, elevated oblique camera from the corner outside the baseline, shows the entire correct rectangular tennis court, architectural sports editorial photography. Bright soft natural daylight. No people, balls, logos, text, watermarks, building signs or UI. Court surface must be BLUE, not green or orange. Fictional generic sports venue, no identifiable location. Elegant clean composition, realistic court geometry.

### Mini soccer card

Use case: photorealistic-natural. Asset type: wide landscape editorial sports photograph for a mini soccer booking card. Close low angle view of a pristine lush short green artificial turf mini soccer pitch with a white sideline, one classic black and white football at the lower right, a small unbranded white goal and dark blue perimeter fencing softly visible in the background. Bright natural late afternoon daylight, believable sport texture and depth of field. Strong horizontal composition 3:2, polished sports editorial photography. No people, text, logos, watermark, or UI. This is an illustrative fictional sports court, not a real identifiable location.

## Fonts

Fonts are hosted locally, emitted by Vite, and preloaded on the home route only. No Google Fonts runtime requests are made.

- [Barlow Condensed Bold](https://github.com/google/fonts/tree/main/ofl/barlowcondensed): `resources/fonts/BarlowCondensed-Bold.ttf`.
- [Manrope Variable](https://github.com/google/fonts/tree/main/ofl/manrope): `resources/fonts/Manrope-Variable.ttf`.
- SIL Open Font License copies: `resources/fonts/licenses/`.

## Footer payment logos

The eight non-generated payment marks are downloaded from [Midtrans's official payment-method page](https://midtrans.com/features/payment-methods) and hosted locally in `public/images/payments/`. Exact asset URLs and retrieval date are in `public/images/payments/sources.json`. Original colors/aspect ratios are preserved. BRI uses the BRIVA virtual-account mark supplied by Midtrans. No logo is synthesized or reconstructed as text.

The footer defers actual method availability to checkout; adding a logo does not configure a merchant payment channel. Verification: all eight images loaded at 320/390/768/1440px without page overflow; footer axe scans at desktop/mobile reported no violations. Home feature tests, Vite build and strict premium audit passed.
