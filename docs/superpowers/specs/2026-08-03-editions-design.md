# Editions — multi-year support with archived recaps

**Date:** 2026-08-03
**Status:** approved by Bart (interactive brainstorm)

## Goal

MBLAN runs a new edition every year. The live site always shows the **active
edition** exactly like the single-event site works today. Past editions stay
browsable forever through a **recap page per edition**, rendered in that
edition's colors. Achievements span all editions. Starting a new edition
(MBLAN27) must be: create an edition in Filament, flip it to active.

## Decisions made

- **Archive UX:** one recap page per edition with component sections
  (sliders for timeline photos, news, tournament results, leaderboards) — not
  a full frozen copy of the site.
- **Theming scope:** colors + a few tokens (logo, hero image, tagline) per
  edition. Layout, fonts, and the forge/wood look stay shared. The existing
  `--c-primary-*` CSS variables are the mechanism.
- **Entree game:** per-edition results in a new table; each edition can ship a
  new/tweaked game; old games are not playable, only their results survive.
- **Architecture:** edition scoping in the live data model (`edition_id` FKs),
  no snapshotting, no recap caching.

## Data model

### New table `editions`

| column | type | notes |
|---|---|---|
| `name` | string | "MBLAN26" |
| `year` | unsignedSmallInteger | 2026 |
| `slug` | string unique | "mblan26", route key |
| `is_active` | boolean | exactly one active; enforced by the admin action, not the DB |
| `primary_color` | string | base hex, e.g. `#37c26f`; 50–950 shades derived from it |
| `logo_path` | string nullable | |
| `hero_image_path` | string nullable | |
| `tagline` | string nullable | |
| `starts_at` / `ends_at` | date nullable | informational only |

### `edition_id` foreign key (non-nullable after backfill) on

`tournaments`, `schedules`, `photos`, `news`, `signups`.

Children inherit scope via their parent — **no** `edition_id` on
`schedule_blocks`, `tournament_rounds`, `tournament_round_scores`,
`tournament_registrations`, `tournament_user`, `reactions`.

### New table `game_results` (replaces barn columns on `users`)

`user_id`, `edition_id`, `catches`, `completed` (bool), `time_ms` (nullable),
unique on (`user_id`, `edition_id`). `POST /game/sync` upserts the row for the
active edition. `users.barn_catches/barn_completed/barn_time_ms` are migrated
in, then dropped.

### Stays global (no edition scoping)

`users` (incl. beer/wine counters), `achievements` + `achievement_user`,
`games` (shared catalogue; only tournaments of a game are per-edition),
`pizza_rounds`/`pizza_orders` (operational), `reactions`, `tags`, `media`.

### Backfill migration

Creates edition MBLAN26 (year 2026, slug `mblan26`, active, color `#37c26f`),
assigns every existing tournament/schedule/photo/news/signup to it, copies
barn stats into `game_results`.

## Active edition scoping

- `Edition::current()` — the single `is_active` edition, memoized per request.
- Trait `BelongsToEdition` on Tournament, Schedule, Photo, News, Signup:
  `edition()` relation, `scopeForEdition(Edition $edition)`, and auto-fill of
  `edition_id` with the current edition on create when unset.
- **Explicit scoping, no global scope.** Each public entry point adds
  `->forEdition(Edition::current())`: ScheduleController, TournamentController
  + `Livewire/Tournament/Ladder`, TimelineController + `Livewire/Timeline/Feed`,
  NewsController, game sync, signup flow. A global scope would silently break
  the Filament admin and the recap page.
- Filament admin sees all editions; Tournament/Schedule/News resources get an
  edition column + filter.
- Achievements keep evaluating across all data; `JOIN_EDITION_*` achievements
  can now use `signups.edition_id`.

## Recap pages + theming

- `/edities` — edition cards (logo, color, year). `/edities/{edition:slug}` —
  recap page: hero (logo, tagline, year) + read-only sections with sliders:
  timeline photos (reusing the lightbox), news, tournament podium + final
  standings, Arti game top-10. Behind `auth:sanctum + verified` like the rest.
- `<x-edition-theme :edition>` Blade component emits the `--c-primary-50…950`
  variables, generated from `primary_color` by a PHP shade-generator support
  class (`App\Support\EditionPalette`). The main layout uses it with the
  current edition (live site recolors on flip); the recap page uses the
  archived edition. Hardcoded values in `app.css` remain as fallback.
- "Edities" link in the nav.
- Opening `/edities/{slug}` for the **active** edition redirects to `/schedule`
  (the live site *is* that edition); its card on `/edities` is labeled
  "huidige editie".

## Admin

Filament `EditionResource`: CRUD, color picker, logo/hero upload, "Maak
actief" action that activates one edition and deactivates the rest.

## Migration order

1. Create `editions`, insert MBLAN26 (active).
2. Add nullable `edition_id` to the five tables → backfill to MBLAN26 → make
   non-nullable with FK.
3. Create `game_results`, copy barn stats, drop barn columns on `users`
   (update `/game/sync` + `ArtiLeaderboardWidget` in the same change).

## Testing

Pest feature tests (RefreshDatabase, sqlite :memory:): per-page active-edition
scoping (old-edition data invisible on live pages), recap page renders an
archived edition's data, game sync writes per-edition results, "Maak actief"
flips exactly one edition. Factories updated so existing tests keep passing
(EditionFactory + auto-attach current edition).

## Out of scope

Full per-edition themes (fonts/layout), playable archived games, recap
caching, per-edition pizza history, public (unauthenticated) archives.
