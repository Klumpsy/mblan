# Edition-themed showcase front page — design

**Date:** 2026-08-17
**Status:** Approved (design), ready for implementation planning

## Summary

Replace the "Arti in Space" mini-game currently at `/` with a striking,
edition-themed **showcase** landing page: a cinematic, atmosphere-first splash
driven entirely by the active `Edition`. The game is preserved and relocated to
its own route. The showcase's single call-to-action is "Doe mee" (join the
active edition), reusing the existing authentication modal.

This is a "pure theme showcase" — atmosphere first, minimal functional content,
one CTA — not an entry hub or a marketing funnel.

## Goals

- A "super nice", edition-themed front page that celebrates the current edition.
- Fully **data-driven** from the active `Edition` so a future edition (MBLAN27,
  new theme) works from the Filament admin with no code change.
- Degrade gracefully: the active edition today has most theme fields empty, so
  every field needs a sensible fallback.
- Preserve the "Arti in Space" game and its leaderboard — nothing is lost.

## Non-goals

- No entry hub / no site navigation on the showcase (that stays behind login on
  the rest of the site).
- No new database columns and no new Filament form fields.
- No farm (or other non-space) backdrop art in this iteration — deferred.
- No new signup flow — the CTA reuses the existing auth modal.

## Decisions (from brainstorming)

| Question | Decision |
|----------|----------|
| Purpose | Pure theme showcase — atmosphere first, one CTA |
| Game fate | Keep it, move it to its own route |
| Primary CTA | "Doe mee" — join the active edition (reuses existing auth modal) |
| Content blocks | Live countdown + logo/tagline hero + animated theme scene (no dates/location strip) |
| Approach | A — data-driven themed splash |
| Layout | C — cinematic title screen (full-bleed scene, bottom-anchored text) |
| Countdown | Full lifecycle (no-date / upcoming / live / over) |
| Backdrop theming | Space backdrop now, keyed by `scenery_set`; palette-gradient fallback for others; farm deferred |

## Architecture

### Routing

- `routes/web.php`: `Route::view('/', 'index')` becomes
  `Route::view('/', 'landing')->name('home')` (public).
- New **public** route for the game:
  `Route::view('/spel', 'spel')->name('spel')`. Public, matching the game's
  current public access. The `game.sync` POST route is unchanged (stays inside
  the `auth:sanctum` group — sync only fires for logged-in players).

### Views

- **`resources/views/landing.blade.php`** — new. Standalone full HTML document
  (own `<head>`, `@vite`, `<x-edition-theme />`, `@livewireStyles/Scripts`),
  same shell pattern as the current `index.blade.php`. No `x-app-layout`, no
  site nav.
- **`resources/views/spel.blade.php`** — the current `index.blade.php` content
  (the space game) moved essentially verbatim. Keeps its `spaceClassic` Alpine
  mount, sprite URLs, boss registry read, `game.sync` config, and the existing
  login modal/HUD.
- `resources/views/index.blade.php` — removed (its content lives on in `spel`).

### Landing page composition (layout C — cinematic)

Layered, full-bleed, `overflow-hidden`:

1. **Backdrop layer** (furthest back): themed starfield gradient using the
   edition palette (`--c-primary-*`). Keyed off `scenery_set`:
   - `space` (and default/fallback): starfield + dominant planet near top.
   - other sets: clean palette-gradient starfield (no bespoke art yet).
   - Structured as a swappable partial (e.g. `_backdrop-space.blade.php`) so a
     future `_backdrop-farm.blade.php` can be added without touching the rest.
2. **Sprite layer**: the edition's `scenery_sprites` (or built-in
   `ScenerySets` fallback images) drifting with light parallax /
   `sprite-bob`-style motion. A small Alpine component or pure CSS animation.
3. **Content panel** (bottom-anchored, over a bottom-up gradient scrim):
   - Wordmark from `Edition::currentBrand()` — base + accent
     (`primary-*` green), OR the logo image if `logo_path` is set.
   - Tagline (`tagline`, with fallback).
   - Name + year.
   - **Countdown** (see states below).
   - **"Doe mee"** CTA button (`btn-wood clip-corner`).
   - Small "Speel Arti in Space" link → `route('spel')`.

### Data flow (all from `Edition::current()`, all with fallbacks)

| Field | Source | Fallback when empty |
|-------|--------|--------------------|
| Wordmark | `Edition::currentBrand()` | Already returns `["MBLAN", accent]` |
| Logo | `logo_path` | Wordmark (no image) |
| Tagline | `tagline` | Current space tagline: "Arti en de boer, de ruimte in" |
| Name / year | `name`, `year` | `currentName()` → "MBLAN" |
| Palette | `<x-edition-theme>` `--c-primary-*` | Component already defaults to forge green |
| Countdown | `starts_at` / `ends_at` | "Datum volgt" |
| Backdrop | `scenery_set` | `space` visuals / palette-gradient fallback |
| Sprites | `scenery_sprites` → `ScenerySets` | Built-in set images (`images/space/*` etc.) |

### Countdown — full lifecycle

A small Alpine component given `startsAt` / `endsAt` (ISO strings, or null),
computed server-side from the active edition:

- **No date set** (`starts_at` null): render "Datum volgt", no ticking.
- **Upcoming** (`now < starts_at`): live ticking countdown (days / hours /
  minutes / seconds) to `starts_at`.
- **Live** (`starts_at <= now <= ends_at`): "NU BEZIG" live badge instead of
  numbers. If `ends_at` is null, treat any time at/after `starts_at` as... see
  Edge cases.
- **Over** (`now > ends_at`): "Tot ziens — bekijk de recap" linking the
  edition recap (`route('editions.show', $edition)` / schedule as today).

### CTA — reuses existing auth modal

The "Doe mee" button opens the exact login/register modal already present in
`index.blade.php` (Discord OAuth primary, email login fallback, register link).
For logged-in members the CTA becomes "Betreed De Schuur" → `route('schedule')`,
identical to current behaviour. No new signup flow is built.

### Admin

No changes to `EditionResource` — its form already edits `tagline`,
`logo_path`, `hero_image_path`, `primary_color`, `palette`, `scenery_set`,
`scenery_sprites`, `starts_at`, `ends_at`. Making the showcase look great is a
content task: fill those fields for the active edition.

## Edge cases

- **All theme fields empty** (current dev state): page still renders — wordmark
  + fallback tagline + "Datum volgt" + CTA over the default palette-gradient
  starfield. Never a broken/blank page.
- **`starts_at` set but `ends_at` null**: upcoming countdown works; once
  `now >= starts_at`, show "NU BEZIG" (there is no "over" boundary without an
  end date). Documented so behaviour is predictable.
- **No active edition** (`Edition::current()` null): fall back to
  `currentName()`/`currentBrand()` defaults and the space backdrop; countdown
  shows "Datum volgt".
- **`scenery_set` other than `space`** with no bespoke backdrop: palette-gradient
  starfield fallback (still on-theme via palette), sprites from the set.

## Testing

**Pest feature tests:**

- `/` renders the wordmark and the "Doe mee" CTA.
- `/` with no active edition still renders (fallbacks) and returns 200.
- Countdown states: seed an edition with `starts_at` in the future → countdown
  markup present; within `starts_at`..`ends_at` → "NU BEZIG"; past `ends_at` →
  recap link; null `starts_at` → "Datum volgt".
- `/spel` returns 200 and mounts the game (contains the `spaceClassic` root).
- `game.sync` still behaves as before (unchanged; existing tests cover it).

**Browser verification** (per project workflow, `verify-in-browser`): serve the
app, load `/`, confirm the cinematic layout, the drifting sprites, the ticking
countdown, the "Doe mee" modal opening, and the `/spel` link launching the
game. Screenshot desktop + mobile widths (mobile-first).

## Files touched (summary)

| File | Change |
|------|--------|
| `routes/web.php` | `/` → `landing`; add public `/spel` → `spel` |
| `resources/views/landing.blade.php` | new — showcase |
| `resources/views/spel.blade.php` | new — game (moved from `index`) |
| `resources/views/index.blade.php` | removed |
| `resources/views/partials/_backdrop-space.blade.php` (or similar) | new — swappable backdrop |
| Countdown Alpine component | new — in `resources/js` + registered in `app.js` |
| Pest tests | new feature tests for landing + `/spel` |

`space-classic.js`, the `space-classic/` folder, `bosses.json`, `GameResult`,
the leaderboard widget, and `game.sync` are all unchanged.

## Deferred / follow-ups

- Farm (and other) bespoke backdrops keyed by `scenery_set`.
- Optional richer motion (e.g. reusing a lightweight starfield canvas) if the
  CSS/Alpine parallax feels flat in browser verification.
