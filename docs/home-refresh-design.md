# Home refresh — blue court direction

Branch: `design/home-blue-refresh`. Scope: home page and its header/footer variant only. The catalogue, booking, account and admin flows retain their current owners and behavior.

## Brief and direction

Indonesian first-time visitors need to understand what can be booked, where to see availability, and what happens after choosing a time. The supplied blue pickleball and green tennis presentation references inform the court photography, white court markings, generous type and disciplined composition. They are visual references, not venue photographs or product copy.

Palette: court blue `#1455d9`, deep blue `#102c59`, ice `#edf4ff`, white `#ffffff`, slate `#526581`, ball yellow `#d6f05a`. Blue is the action/brand color; yellow is confined to sports imagery and small decorative details. Existing danger/warning colors retain their meaning.

Type: locally hosted Barlow Condensed bold for the main sporting headline and section headings; locally hosted Manrope for navigation, prose and prices. Sentence-case headings, no tracked uppercase section labels. Paragraphs stay within 60 characters per line.

## Layout exploration

Option A — split illustration and copy:

```text
logo                     navigation / account
headline + CTA           court illustration
sport choices            repeated featured cards
steps                    long terms
```

This is too close to the current homepage and repeats the decision to choose a sport. Reject it.

Option B — one photographic court entrance, chosen:

```text
logo          Beranda / Lapangan / Cara booking        account
+-----------------------------------------------------------+
| large left-aligned headline    blue court / ball photograph |
| concise explanation / see availability                      |
+-----------------------------------------------------------+
| opening hours          duration          payment choice    |

Pilih lapanganmu.                         all courts
[court + price + view schedule] x dynamic featured results

Booking pertama? Ikuti tiga langkah.   [1] choose [2] pay [3] play
FAQ disclosures / simulation notice / quiet footer
```

Mobile preserves the same reading order. Navigation uses an explicit native disclosure on narrow viewports. The main CTA goes to the existing court catalogue; each court link goes to its existing schedule/detail. Displayed hours, DP percentage, names and prices come from existing server data. No fabricated availability, ratings or venue claims.

## Review against the brief

The photographic court entrance is the single expressive element. A generic floating search widget, metric cards, decorative gradients, repeated sport selectors and sales badges were rejected because they add decisions before the visitor can see a schedule. Court lines, the ball and athletic typography provide the identity; the supporting content uses plain white surfaces, calm spacing and clear actions.

## Token ownership and rollback

### Featured court card refresh

The user rejected the plain white catalogue cards. Retain court blue `#1455d9`, navy `#102c59`, ice `#edf4ff`, white `#ffffff`, slate `#526581` and the established Manrope/Barlow Condensed pair. Use a sports-venue poster composition: a dominant photograph with a readable navy-backed court title, then a compact facilities/price/action panel. All text is left aligned; image and action dimensions are reserved. No ratings, availability badges, fabricated discounts or decorative numbering.

```text
[sport]                    [sport icon]
              court photograph
Court name in athletic display type
Surface type
--------------------------------------
actual facilities
Tarif per jam       [Lihat jadwal]
Rp100.000
```

A more ornate white card with shadows and extra labels was rejected because it preserves the current generic composition. The chosen signature is the photograph/title pairing; supporting information is compact and quiet. Mobile stacks the same information without hiding actions. Titles support long names; prices and facilities remain server data. The image overlay exists for text contrast, not decorative color.

Model B: `resources/css/home.css` owns the home theme and remaps the established semantic variables under `.home-page`. `DESIGN.md` records the accepted home variant. It is imported only on the home route. Existing application tokens and components remain canonical elsewhere. Keep the work on this branch; returning to `main` restores the previous homepage.

## Verification — 2026-10-22

- `php artisan test`: 87 tests, 320 assertions passed, including dynamic home settings and the no-active-courts state.
- `npm run build` with temporary Node 22.20.0: passed. Fonts and home CSS are versioned Vite assets; no unresolved asset warnings remain.
- `npm run test:browser` against an isolated local demo database: 15 checks and 8 axe scans passed; no page errors. Existing customer booking, account validation, cancellation dialogs and admin CRUD were exercised.
- `npm run test:home` against that isolated demo: 13 checks and 5 axe scans passed, with no axe violations or page errors. Coverage includes 320/390/768/1024/1440px, keyboard menus/FAQ, customer/admin navigation, long court names and reduced motion.
- Premium strict static audit: zero findings. Evidence is generated under ignored `output/home-refresh/`.
- Official DESIGN.md lint: zero errors; six pre-existing unused-token-reference warnings remain in the legacy frontmatter component definitions.
- Pint for the changed PHP test file: passed. Repository-wide Pint reports existing formatting findings in seven unchanged controller/service/test files; those were outside the home design scope.
- Docker build/startup: passed; app healthy and scheduler running. Read-only Chrome inspection of the Docker homepage on port 8000 returned HTTP 200, loaded local fonts, and confirmed desktop/mobile layout and a clickable logo with the mobile menu expanded.
- Clean screenshots were inspected after refinement. The initial narrow hero was shortened, secondary text enlarged, and the mobile disclosure moved out of the navbar to avoid overlapping the logo.

Verification used Linux Chrome. Windows, physical touch devices, a full 200% zoom matrix and screen-reader usability testing were not performed. Automated accessibility scans are evidence, not an accessibility certification.

## Review and rollback

Maintenance entry points:

- `resources/views/components/home/navbar.blade.php`: desktop/mobile navigation and account-menu placement.
- `resources/views/components/home/footer.blade.php`: footer identity, links and copyright.
- `resources/views/components/home/payment-methods.blade.php`: payment logo list and caption, composed by the footer.
- `resources/views/components/customer-account-links.blade.php`: shared guest/customer/admin account links.
- `resources/views/layouts/home.blade.php`: page shell, assets, feedback and content slot; composes the navbar/footer components.
- `resources/css/home.css`: existing theme and responsive styling, shared by these components.

Run the current branch with `docker compose up --build -d`, then open http://127.0.0.1:8000. To restore the original design without discarding this branch:

```sh
git switch main
docker compose up --build -d
```

This refresh introduces no database migrations or payment/business-rule changes. The preview images, court descriptions and venue remain simulated.

Navbar/footer extraction verification: all 87 application tests passed. The rendered navbar/footer DOM and screenshot hashes before/after extraction matched at 1440px and 390px; the mobile menu opened successfully. The strict premium audit reported zero findings, and Docker rebuilt and started successfully. This is a structure-only refactor; existing CSS and booking behavior are retained.

Featured court refresh verification: 87 application tests passed. Vite and Docker builds succeeded. Read-only Chrome checks at 320/390/768/1024/1440px found no page overflow or clipped card panels; all images loaded, schedule navigation reached the existing court route, long names wrapped, and reduced-motion disabled image transitions. Desktop/mobile axe scans reported zero violations. The strict premium audit reported zero findings, and DESIGN.md lint returned zero errors with the six existing legacy token-reference warnings. Desktop and actual mobile viewport screenshots were inspected; prices and actions remain readable.

### Booking guide refresh

Retain blue #1455d9, navy #102c59, ice #edf4ff, white #ffffff, slate #526581 and the existing Manrope/Barlow pair. Use a navy sports playbook: left-aligned headline and introduction above three open numbered lanes, with court-line geometry joining the real sequence. White body text and a white catalogue action provide contrast. No moving decoration or simulated booking status.

```text
Chosen: [Heading                    Intro + catalogue action]
        [1 ——————— court service line —————— 2 —————— 3]
        [Choose schedule] [Review and payment] [Confirm and play]
Mobile: heading, intro/action, then 1 → 2 → 3 vertically.
Alternative: three floating icon cards — rejected; repeats a generic card kit.
```

Critique: the old timeline is clear but visually anonymous. The new signature is one continuous court-like panel, not three boxes. Numbering conveys the actual order. Limit decorative court marks to the sequence rail; preserve payment verification and dynamic deadlines. Component owner: `components/home/booking-guide.blade.php`; styling remains in home.css.

Booking guide verification: all 87 application tests (320 assertions) and the production Vite build passed. Docker rebuilt successfully; app and scheduler are healthy. Read-only Chrome checks at 320/390/768/1024/1440px found no page overflow or clipped steps. Desktop/mobile axe scans returned zero violations; keyboard Enter on the catalogue action reached /courts, reduced motion retained all steps, and no page errors occurred. Desktop and mobile screenshots were inspected. Strict static audit: zero findings; DESIGN.md lint: zero errors and six existing legacy token warnings. This change introduces no booking logic or payment state changes.
