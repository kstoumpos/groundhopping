# Athens Groundhopper

A classic (non-block) WordPress theme guiding fans to football around
Athens — Champions League nights, non-league Sundays, women's football —
with a fan-funded AED campaign for grounds that still need a
defibrillator.

## Setup

```bash
npm install
npm run build   # compiles src/input.css -> assets/css/tailwind.css
```

Re-run `npm run build` after editing any template or `template-parts/`
file — the compiled CSS is committed (WordPress hosting has no Node build
step), so this is a required part of every deploy, not optional tooling.

## Structure

- `header.php` / `footer.php` / `front-page.php` / `index.php` /
  `single-ground.php` / `single-campaign.php` / `archive-ground.php` —
  classic PHP templates, standard template hierarchy (no registration
  needed for the single-/archive- ones, just the filename)
- `template-parts/` — the campaign progress bar and the Weekend Radar
  fixture card, each a plain `get_template_part()` include
- `functions.php` — theme setup, the four custom post types (Ground,
  Club, Fixture, Campaign), the `competition` taxonomy, meta fields, and
  helper functions (`ag_format_eur()`, `ag_aed_status_label()`,
  `ag_competition_pill_classes()`)
- `inc/class-ag-cli-import.php` — WP-CLI content importer, see
  `data/README.md`
- `data/*.csv` — empty import templates, one per post type
- `tailwind.config.js` / `theme.json`-replacement — design tokens as
  Tailwind theme keys, and as classic `editor-color-palette` /
  `editor-font-sizes` support in `functions.php`, so the block editor's
  swatches stay on-brand when writing a Ground or Campaign's body text

## Content

Nothing is seeded — see `data/README.md` for the `wp ag-import ...`
workflow once there's real data to load (from the union partnerships, or
typed in by hand).

## Known gaps, by design

- Match Finder filters and the EN/GR toggle on the homepage are
  presentational — no JS or query-param wiring yet
- Donation amount buttons on a campaign page don't charge anything yet —
  payment integration (Viva Wallet vs Stripe, per the data-sourcing
  research) is a separate build step
- Fonts load from Google Fonts' CDN, which sends EU visitors' IPs to
  Google — self-host before launch (see the comment in `functions.php`)
