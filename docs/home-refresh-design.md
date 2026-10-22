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

### Sticky home navigation

Keep the accepted white/blue navbar, typography and spacing. Use CSS sticky at top: 0, with a subtle divider and document scroll padding for anchor destinations. Preserve normal document flow so the hero does not jump. The mobile disclosure closes when navigating and supports internal scrolling on short viewports; no scroll animations or extra controls.

Sticky navbar verification: home tests (2 tests, 8 assertions), production build and Docker rebuild passed. Chrome at 320/390/768/1440px confirmed header top stays at 0 while scrolling, anchor targets remain below the navbar, mobile navigation closes, and no horizontal overflow occurs. Header axe scans at 390/1440px returned zero violations; screenshot inspected. Strict static audit returned zero findings; DESIGN lint has zero errors with the six existing legacy warnings.

### Minimal booking guide revision

The user found the bordered playbook too busy. Retain navy #102c59, blue #1455d9, white #ffffff, ice #edf4ff and slate #526581, with the existing Barlow heading and Manrope reading text. Simplify to three open, left-aligned steps: an unframed number beside a short heading, then one paragraph. Preserve the navy section and catalogue action. One section divider provides structure; remove the cell borders, connector rails, icon row, circular number badges and secondary captions.

```text
Chosen:   1  Pilih jadwal       2  Pesan dan bayar       3  Bersiap main
             paragraph             paragraph                paragraph
Mobile: same order, stacked with open space.
Rejected: smaller outlined cards — still repeats the visual clutter.
```

Critique: sports identity already comes from Barlow typography and the blue court palette. Extra court marks add no help to this instructional section. Shortening the headings and merging repeated captions into the paragraphs improves scanning while preserving login, DP, verification and balance deadlines.

Minimal guide verification: home tests passed (2 tests, 8 assertions), Vite build passed, Docker app/scheduler healthy. Chrome checks at 320/390/768/1024/1440px found no horizontal overflow or clipped steps. Desktop/mobile axe scans returned zero violations; keyboard navigation reached the catalogue, reduced motion preserved all steps, and no page errors occurred. Both screenshots inspected: open spacing and shorter headings remove the earlier crowded treatment. Strict audit: zero findings. DESIGN lint: zero errors, six existing legacy token warnings.

### Blue court catalogue

Extend the approved home theme to /courts on the same design branch. Palette: blue #1455d9, navy #102c59, ice #edf4ff, white #ffffff, slate #526581. Local Barlow Condensed owns the athletic page/court headings; Manrope owns filters and reading text. Left-align all content. Reuse layouts/home, shared navbar/footer, booking-steps, field and the home court-card component. Native sport select and explicit GET search remain canonical; nine results per page with query preservation.

```text
[shared sticky navigation]
[pale blue: booking stages / Pick your court / short guidance]
[name search            sport select       Search / Reset]
[Available courts                          result count]
[photo court card] [photo court card] [photo court card]
[pagination / illustration disclaimer]
[shared footer]
```

A second photographic hero was rejected: photos already provide identity in the reusable court cards, and the catalogue should reach the filters quickly. No new decorative badges or availability claims. Empty results and validation retain visible recovery. Shared navbar resolves the current route and links back to the home guide from the catalogue. Only catalogue joins the blue shell; schedule/checkout keep their existing implementation in this task.

Catalogue verification: all 87 PHP tests (320 assertions) and production Vite build passed. Full browser flow passed 15 checks/8 axe scans against isolated SQLite, including customer booking/cancellation and admin CRUD. Home regression passed 13 checks/5 axe scans after updating its expected catalogue theme; full-flow logout now opens the shared account menu. Dedicated catalogue inspection covered 320/390/768/1024/1440px, search/clear/sport filters, empty state, validation with focused summary, schedule keyboard navigation, home guide links, long names and reduced motion. Three catalogue axe scans and an additional 320px pagination scan returned zero violations. Eleven temporary fixture courts verified the 9/2 page split, previous/next boundaries, retained query/filter/page and keyboard navigation. Fixture data exists only in the temporary test database. Desktop/mobile screenshots were inspected. Docker app/scheduler are healthy; read-only inspection confirmed blue tokens and loaded footer payment logos. Strict audit: zero findings; DESIGN lint: zero errors, six existing legacy token warnings. No payment logic, schema or customer data changed; schedule/checkout retain their previous theme.

### Sports login refresh

Extend the accepted blue identity to /login only. Palette: court blue #1455d9, navy #102c59, white #ffffff, ice #edf4ff, slate #526581. Barlow Condensed owns the athletic headings; Manrope owns labels and form text. Reuse the blue shell and canonical field/feedback/password-toggle/submit behavior. Left-align all form content.

```text
[shared blue navigation]
[court photograph + short sports headline | focused email/password form]
[                                        | remember / forgot / sign in]
[                                        | create account]
[shared footer]
Mobile: focused form; omit the nonessential photo panel.
```

Reject a floating form card over a full-screen photograph: it competes with the inputs and introduces unnecessary overlay styling. Spend the visual emphasis on one reserved photo panel; keep the form plain white without shadows or badges. Feedback belongs inside the form column above its fields. Existing session, rate-limit, generic credential errors and intended redirect remain server-owned. Registration and password recovery retain their existing screens in this scoped task.

Login verification: 87 application tests (320 assertions) and Vite build passed. Full isolated browser regression passed 15 checks/8 axe scans, including customer/admin login and booking/admin flows. Dedicated login checks passed at 320/390/768/1024/1440px with no page overflow; three axe scans reported zero violations. Empty/invalid submissions focused canonical feedback, preserved email/remember and cleared the password. Keyboard password visibility, unchanged submit dimensions/busy label, recovery/register links and intended /profile redirect passed. Desktop and mobile/error screenshots were inspected. Docker rebuilt with healthy app/scheduler. Strict audit: zero findings; DESIGN lint: zero errors, six existing legacy token warnings. Registration/recovery, authentication rules and database schema remain outside this visual change.

### Booking history blue refresh

Adopt the accepted sports palette: blue #1455d9, navy #102c59, ice #edf4ff, white #ffffff, slate #526581. Barlow Condensed owns the page/court headings; Manrope owns dates, status and form text. Left-align content, with the existing home blue shell and a short pale-blue heading.

```text
[Booking saya                         Cari lapangan]
[status select / Tampilkan / reset     count]
[court + code/status | date/time | total | detail]
[court + code/status | date/time | total | detail]
[query-preserving pagination]
Mobile: court/status → schedule/deadline → total/detail.
```

Photo cards and summary metric tiles were rejected: this is a schedule-management page, so they would distract from the actual booking data. Use open match-schedule rows with one separator per booking. Reuse the canonical status badges, native select, feedback, and blue pagination (parameterized noun). Preserve booking snapshots, server ownership/expiry, ten items per page, creation order, filter URL and detail/payment/cancellation routes. Only active held/partial bookings expose their existing deadlines; cancelled/expired bookings never imply an amount is still payable. Empty filter results offer reset; a new customer receives a court-catalogue action.

Booking history verification: all 87 application tests (320 assertions), Vite production build and Docker rebuild passed. Full browser regression against isolated SQLite passed 15 checks/8 axe scans, including customer booking/cancellation and admin CRUD. A dedicated history check used 16 own booking snapshots plus one foreign booking, with held/partial/paid/cancelled/expired states; it verified no foreign data, active-only deadlines, 10-row pagination, filter query preservation, previous/next keyboard navigation, detail navigation, focused invalid-status feedback and distinct empty/filter-empty recovery. Desktop/mobile and empty-state screenshots were inspected. Widths 320/390/768/1024/1440px had no page overflow, including a long court name; three additional axe scans returned zero violations. Strict audit: zero findings; DESIGN lint: zero errors and six existing legacy token warnings. Fixtures remain only in /tmp test SQLite; production booking data, rules and controllers were not changed. Admin history retains its canonical table.

### Blue court schedule

Refresh /courts/{court} through its shared template, including the requested /courts/3. Retain blue #1455d9, navy #102c59, ice #edf4ff, white #ffffff, slate #526581; semantic warning/success colors stay canonical. Barlow Condensed for court and section headings, Manrope for fields, times and amounts. Left-align reading content.

```text
[shared sticky blue navigation]
[booking stages]
[court name / sport / hourly rate          small court photograph]
[date field + show schedule / Today / Tomorrow]
[duration + labelled time radios          | selected time / total / DP]
[court information disclosure            | continue to review]
[shared footer]
Mobile: date → duration/time → summary, without decorative photograph.
```

A full photographic hero was rejected because it delays schedule selection. The compact photo is the single sports accent; the actual controls remain on quiet white surfaces. Extend canonical booking-flow/field/radio/select components through court-schedule styling, retaining data hooks, explicit date submit, stale-date blocking, duration-fit availability and server validation. Native date/select popup behavior stays accepted. Summary sticks below the shared navbar only when the viewport can accommodate it. No new booking/payment states or fabricated availability.

Schedule verification: 87 PHP tests (320 assertions), Vite production build and Docker rebuild passed. Full isolated browser regression passed 15 checks/8 axe scans, including booking/cancellation and admin maintenance. Dedicated /courts/3 checks at 320/390/768/1024/1440px passed with no overflow and three additional axe scans without violations. Covered guest action, reserved/maintenance labels, keyboard radio selection, two-hour total/DP for mini soccer, clearing a selection when duration no longer fits, stale-date submit blocking, fully unavailable day recovery, keyboard court-information disclosure, summary below navbar and static on short screens, long names, reduced motion and successful two-hour booking creation. Fixtures and booking creation exist only in /tmp test SQLite. Desktop/mobile screenshots were inspected. Strict audit: zero findings; DESIGN lint: zero errors with six existing legacy token warnings. Native date/select popups retain OS formatting by the maintained contract. Availability, payment rules and controllers were not modified.

### Court schedule rebuild from supplied presentation reference

The user rejected the previous mini-photo plus several booking panels as confusing. Supersede that composition. The supplied reference uses large condensed sports typography, strong court photography and quiet reading surfaces. Retain court blue #1455d9, navy #102c59, white #ffffff, ice #edf4ff and slate #526581; Barlow Condensed for identity, Manrope for controls. Left-align controls and explanatory copy.

```text
[nav]
[navy court title / tariff | large photograph, like reference slide]
[canonical booking progress]
[one booking sheet]
  date / show schedule / today-tomorrow
  duration
  labelled available-time grid
  selected time → total + DP/balance → continue
  supplementary venue disclosure
[footer]
```

Reject keeping the side summary with different colors: it still divides attention between date above, radios left and amounts right. Use one vertical decision sequence, six time columns on desktop/three on mobile, and no sticky summary. The compact price/action footer is the final step in the same sheet. Payment explanations are shortened without removing hold/no-reschedule rules, DP amounts or confirmation requirements. Native controls, existing form/data hooks and server rules remain canonical; the photograph stays an illustration, not venue evidence.

Reference rebuild verification: 87 PHP tests (320 assertions), production build and Docker rebuild passed. Full browser regression passed 15 checks/8 axe scans. Dedicated court-3 checks at 320/390/768/1024/1440px passed: available/reserved/maintenance radios, keyboard selection, duration-dependent price/DP, invalidated selection for insufficient time, stale-date blocking, a fully unavailable date, venue disclosure, long names, reduced motion and successful two-hour booking. Three additional axe scans returned zero violations; no page errors/overflow. Desktop/mobile screenshots were inspected against the supplied reference: large sports identity/photo, a single quiet booking sequence and a compact amount/action footer replace the rejected multi-panel composition. The summary is now static on every viewport. Strict audit has zero findings; DESIGN lint has zero errors with six existing legacy token warnings. Test bookings/maintenance remain in temporary SQLite only. Native popup formatting and the server payment/availability rules are retained.

### Booking checkout reference rebuild

The user requests the same sports presentation reference for /bookings/3. Apply it to the customer checkout owner, not the admin detail view. Retain blue #1455d9, navy #102c59, ice #edf4ff, white #ffffff, slate #526581; Barlow Condensed owns headings and Manrope owns financial/reading content. Left-align the sheet.

```text
[shared blue navbar]
[navy order title/code | court photograph illustration]
[canonical progress when payable]
[one readable order sheet]
  court / date / time / booking + payment status / deadline
  itemized total / paid / remaining
  payment choices (DP | full), or balance / completed / inactive
  terms + payment action with visible amount
  refund/history/cancellation disclosures
[shared footer]
```

Reject recoloring the old left/right panels: the user still has to reconcile the schedule on one side with payment on the other. Use one vertical review-to-payment sequence instead, with a compact sports identity banner. Preserve all server-owned branches, amounts, deadlines, polling hooks, recovery and confirmation controls. Do not infer payment success from the presentation. No checkout payment submission to a real gateway is part of design verification.

Order checkout verification: all 87 PHP tests (320 assertions) and the production build passed. Full browser regression on isolated SQLite passed 15 checks/8 axe scans, including customer booking, cancellation confirmation and admin CRUD. Dedicated configured-state tests used dummy keys and intercepted reconciliation responses: five widths (320/390/768/1024/1440px) had no overflow; three additional axe scans returned zero violations. Verified visible DP/full/balance payment-button amounts, keyboard radio selection, terms validation with focused summary, paid/inactive/expired/refund/pending states, refund disclosure and safe confirmation/Escape, history keyboard disclosure, long codes and reduced motion. No valid gateway payment was submitted. Payment-unavailable regression passed on a separate unconfigured server. Desktop/mobile screenshots were inspected; the navy sports header and vertical order/payment sheet replace the old side-by-side panels. Refund form disclosure opens automatically for its validation error. Strict audit: zero findings; DESIGN lint: zero errors and six existing legacy warnings. Only test SQLite contains generated fixtures; no schema, permission, payment transitions or real customer bookings were altered.
