# Content data

These CSVs are empty on purpose — headers only, no rows — because real
content is waiting on the union data-sharing partnerships (EPSA, EPS
Piraeus, EPSANA, EPSDA, ESPG) from the earlier data-sourcing research.
Nothing here gets created until you fill in a row yourself or a union
export gets converted into this format.

**Exception: `grounds.csv` is now populated** — 216 grounds from all four
Attica unions, each converted from that union's own field directory:

- **74 from EPSA Athens** and **41 from EPSDA / West Attica** and **32
  from EPSPEIR / Piraeus** (epsath.gr, epsda.gr, epspeir.gr — all at
  `/field/fields_map.php`). Dimensions, capacity and facility answers
  vary meaningfully row to row in all three, so those were trusted and
  imported as given.
- **69 from EPSANA / East Attica** (epsana.gr). Different treatment,
  worth knowing: in EPSANA's export, every single row shows 0×0m, 0
  capacity, and "No" for changing rooms, lighting and stands — not just
  most rows, literally all 69. That's not 69 grounds that all happen to
  lack every amenity; it's EPSANA's form never being filled in, so the
  dimension and facility columns were left blank rather than imported as
  real answers. Importing a uniform "No" across an entire union would
  have been a data-entry artifact presented as a verified fact, which is
  exactly the kind of false negative the AED-status field was designed
  to avoid elsewhere in this schema. Only `title`, `address` (where
  EPSANA gives one — 57 of the 69 don't), and `surface` carried over for
  this batch.

**One real name collision surfaced across the four files**: both EPSA
and EPSPEIR list a ground called `ΜΟΣΧΑΤΟΥ` — genuinely two different
places (different address, different capacity), not a duplicate. The
importer's title-matching now disambiguates by `union` when importing
grounds specifically, so re-importing doesn't merge them into one post.
If a future union's data collides on title *within the same union*,
that's still a real duplicate the importer will (correctly) merge — the
disambiguation only separates different unions.

A few editorial calls apply across all four batches:
- Greek surface names were mapped to `grass` / `artificial_turf` /
  `dirt` (the last one from EPSANA's data — a few of their grounds are
  bare/unpaved pitches, Greek "Ξερό").
- No row from any of the four unions has `lat`/`lng` — none of their
  directories publish coordinates, so none of these will plot on a map
  until that's sourced separately (OSM, per the earlier research).

## Running an import

From the theme folder, with WP-CLI:

```bash
wp ag-import grounds   data/grounds.csv   --dry-run   # preview first
wp ag-import grounds   data/grounds.csv               # then for real

wp ag-import clubs        data/clubs.csv
wp ag-import fixtures     data/fixtures.csv
wp ag-import campaigns    data/campaigns.csv
wp ag-import competitions data/competitions.csv
```

Run them in that order — clubs look up grounds by title, fixtures look up
clubs and grounds, campaigns look up grounds, competitions look up clubs.
`--dry-run` resolves every lookup and validates every row without writing
anything, so run it first on any file you didn't type in yourself.
Re-running the same CSV later (e.g. after a union sends a correction)
updates the existing post by title instead of creating a duplicate — and
for competitions, re-tags a club with its new division rather than
piling divisions up.

Every lookup (a club's `home_ground`, a fixture's `home_club`, a
campaign's `ground`, a competition's `club`) matches by title with a
Greek-aware fallback — accents, case and extra whitespace are normalized
before giving up, since two different union exports have spelled the
same name slightly differently in every batch so far.

`--status=draft` imports everything as drafts instead of publishing
immediately, if you'd rather review before it goes live.

## grounds.csv

| column | required | notes |
|---|---|---|
| title | yes | ground name |
| content | | shown as the ground's page body |
| address | | |
| lat / lng | | decimal degrees |
| surface | | e.g. grass, artificial_turf, dirt — mirrors OSM's `surface` tag |
| capacity | | integer |
| length_m / width_m | | pitch dimensions in metres, as published by the union |
| has_changing_rooms / has_lighting / has_stands | | `yes` or `no`. Blank is left unset (shown as "not recorded"), not imported as `no` |
| union | | which federation this ground's fixtures come from, e.g. epsa |
| osm_id | | OpenStreetMap node/way id, for re-syncing geometry later |
| aed_status | | `confirmed`, `unconfirmed`, or `none-confirmed`. Blank or anything else imports as `unconfirmed` — the importer never guesses "non-compliant" |
| aed_checked_on | | date the AED status was last verified |
| photo_url | | downloaded once and set as the featured image; re-imports skip a ground that already has one |

Example row (for reference only — not present in the CSV):
`Alepotrypa Pitch,"Hillside concrete ground in Kypseli.",Kypseli Athens,37.9922,23.7392,artificial_turf,500,94,60,yes,yes,yes,epsa,,unconfirmed,,`

## clubs.csv

**Populated** — 373 clubs across all four unions (167 EPSA, 81 EPSPEIR,
74 EPSANA, 51 EPSDA), each converted from that union's own `teams.php`
export and matched against `grounds.csv` by normalized title (accents
and case stripped) — every resolvable ground matched with no manual
fixes needed. A club with no `home_ground` means the union's own export
didn't list one, not a failed match. Field availability differs by
union, same as grounds: EPSA and EPSPEIR include email/address, EPSANA
has a ΓΓΑ code instead, and EPSDA's export has neither — just club name
and ground.

| column | required | notes |
|---|---|---|
| title | yes | club name |
| content | | |
| home_ground | | must match a ground's **title** exactly — import grounds.csv first |
| address | | club office / contact address — separate from the ground's own address |
| email | | |
| gga_code | | the club's registration code with ΓΓΑ (Hellenic General Secretariat of Sports), where a union publishes that instead of contact info |
| union | | optional — if blank, taken from the matched ground's own `union` automatically |
| competition | | taxonomy term name(s), comma-separated; created automatically if new |
| photo_url | | club crest/photo |

## fixtures.csv

| column | required | notes |
|---|---|---|
| home_club / away_club | yes | must match club **titles** exactly — import clubs.csv first |
| kickoff | yes | ISO 8601, e.g. `2026-10-18T17:00:00`. Becomes the post's publish date (the theme's "post_date IS kickoff" convention), so it displays correctly with no extra fields read at render time |
| ground | | must match a ground's title |
| home_score / away_score | | leave blank until full-time |
| status | | `scheduled`, `live`, `finished`, or `postponed`. Default: scheduled |
| source | | which feed/union this row came from, for auditing |
| door_price | | free text — prices vary too much to force a number, e.g. "3€–5€" |
| vibe_note | | one or two sentences on what to expect |
| transit_note | | how to get there by metro/bus/tram |
| competition | | taxonomy term name, drives the card's colour tag — anything containing "women" or "wfl" always renders purple regardless of tier |

There's no `title` column — it's always generated as "Home vs Away" to
match the theme's display convention.

## competitions.csv

Tags a club with its league/division, building the `competition`
taxonomy as a two-level hierarchy: **union as the parent term, division
as the child** — e.g. "EPSA Athens" > "Α Κατηγορία 1ος Όμιλος". Chosen
over a flat list because the same division name (e.g. "Α Κατηγορία")
genuinely recurs across different unions — nesting under the union
keeps those distinct instead of merging them into one tag.

| column | required | notes |
|---|---|---|
| union | yes | `epsa`, `epspeir`, `epsana`, or `epsda`. The parent term's display name comes from `ag_union_label()` in `functions.php` — edit there, not in the CSV, if a union's label should read differently |
| division | yes | the child term, exactly as the union names it |
| club | yes | must match a club already imported (see `clubs.csv`) |

This data isn't scraped from the same page as the team list — a club's
own page doesn't say which division it's in. It comes from each
union's **standings page**, one per division
(`/results/display_ranking.php?league_id=N`), which lists every club
under that division by definition. That's a page-per-division scrape,
not a single flat export like grounds or clubs was — expect more rows
of legwork per union here.

Re-running this file replaces a club's competition rather than adding
to it (`wp_set_object_terms` with `append: false`), so updating a club
after a promotion or relegation is just a re-import with the new
division.

## campaigns.csv

| column | required | notes |
|---|---|---|
| title | yes | |
| content | | shown as "why this ground needs an AED" |
| ground | | must match a ground's title. Blank = general fund |
| goal_amount / raised_amount | | plain numbers, no currency symbol |
| currency | | default EUR |
| status | | `active`, `funded`, or `installed`. Default: active |
| installed_on | | shown once status is `installed` |
| photo_url | | |
