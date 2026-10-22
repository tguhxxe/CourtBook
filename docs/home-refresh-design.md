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

Run the current branch with `docker compose up --build -d`, then open http://127.0.0.1:8000. To restore the original design without discarding this branch:

```sh
git switch main
docker compose up --build -d
```

This refresh introduces no database migrations or payment/business-rule changes. The preview images, court descriptions and venue remain simulated.
