# MBLAN26 "The Forge" — Redesign + Dynamic Edition Theming

**Date:** 2026-07-21
**Branch:** `redesign-mblan26-forge`
**Status:** Approved

## Goal

Two coupled deliverables:

1. **Dynamic per-edition color engine** — pick a color (color wheel) on an edition; the whole
   site recolors from that one hex. Replaces the static orange `primary` palette.
2. **Dark, techy "high-tech-in-a-wooden-barn" redesign** of the public + auth pages, seeded with
   rich MBLAN26 "The Forge III" content in Forge Green.

## Decisions (locked)

| Decision | Choice |
|---|---|
| Redesign scope | Public + auth pages fully redesigned. Logged-in pages (dashboard/profile/tournaments/media/teams) inherit new colors + fonts, layout untouched. |
| Accent color source | Whole site follows the **active edition's** picked color. 2026 = Forge Green `#65E59A`. |
| Color mode | **Dark-only.** Remove light theme + theme-toggle. |
| Display font | **Chakra Petch** (headings), **Montserrat** (body). Self-hosted. |
| Seeding | **Fresh rebuild** (`migrate:fresh --seed`) with rich MBLAN26 content; admin re-seeded. |
| Animations | **Rich + performant** — CSS + IntersectionObserver + light canvas. No GSAP. |

## Brand tokens

- Deep Forest `#0E1A16`, Graphite `#1A1A1A` — backgrounds
- Forge Green `#65E59A` — seeded dynamic `primary`
- Gunmetal Silver `#AEB5B3` — metallic text/borders
- Soft Mint Glow `#C7FFE0` — highlight/glow
- Motif: sharp cut-corner geometry (logo bevels) + wood-plank / barn-door framing over dark base,
  green neon seams, anvil/forge accent.

## Part A — Dynamic Edition Theming Engine

- Migration: add `color` (string, hex) to `editions`, default `#65E59A`.
- `Edition` model: add `color` to `$fillable`; helper `activeThemeColor()`.
- Filament `EditionResource`: add `ColorPicker` field (`color`).
- `App\Support\ThemeService`:
  - `paletteFor(string $hex): array` → generates 50→950 shade ramp. Anchor the picked hex at
    500; lighter steps interpolate toward white, darker toward near-black, in a perceptual-ish
    curve so ramps look good. Returns `['50' => 'r g b', ... '950' => 'r g b']` (space-separated
    RGB channels for the `<alpha-value>` syntax).
  - `activeColor(): string` → color of the active edition, cached; falls back to `#65E59A`.
- `tailwind.config.js`: `primary.{50..950}` → `rgb(var(--c-primary-{shade}) / <alpha-value>)`.
- `<x-theme-vars>` component: emits `:root{--c-primary-50: r g b; …}` from `ThemeService`.
  Included in `layouts/app`, `layouts/guest`, and `index`.
- Cache invalidation: clear the theme cache when an edition is saved (model `saved` event).
- Fallback: no active edition → Forge Green, so the site never renders unstyled.

## Part B — Design System

Self-hosted fonts (Chakra Petch, Montserrat) under `resources/fonts` + `@font-face` in `app.css`.
`tailwind.config.js`: `fontFamily.display = ['Chakra Petch', ...]`, `sans = ['Montserrat', ...]`.

Reusable Blade components under `resources/views/components/forge/`:

- `btn` — variants primary/ghost/anvil; cut-corner clip-path; shine sweep on hover.
- `card` — glass + metallic bevel edge; green seam glow on hover.
- `section` — consistent vertical rhythm + `x-reveal` scroll hook.
- `heading` — Chakra Petch, angular accent bar.
- `badge`, `stat` tile, `divider` (barn-plank / forge-seam), `embers` (hero canvas host).

Shared utilities in `app.css` `@layer components`: `.clip-corner`, `.neon-glow`, `.metal-edge`,
`.wood-panel`, `.forge-seam`.

## Part C — Pages (public + auth)

- **Landing (`index`)** — hero (MBLAN26 logo, ember canvas, "Forged in the Barn", event info,
  CTAs) + scroll sections: brand story, past editions, featured games, latest blog.
- **Editions** index + show — reskinned; each edition themed to its own color on its pages.
- **Blogs** index/show, **Games** index/show, **Achievements** — reskinned.
- **Auth** (login, register, forgot/reset, 2FA, verify) — barn/forge card via `layouts/guest`.
- **Nav + `layouts/app`** — dark techy navbar, metallic active states, animated mobile menu.

## Part D — Animations

- `x-reveal` Alpine directive wrapping one shared IntersectionObserver (fade/slide/stagger).
- Hero forge-ember `<canvas>` — few-hundred particles, rAF, `prefers-reduced-motion` aware.
- Glow-pulse accents, button shine sweep, card tilt/parallax (Alpine + CSS transforms).
- Animated gradient forge-seam dividers.
- `prefers-reduced-motion` disables all motion.

## Part E — Seeding (fresh rebuild)

- MBLAN26 "The Forge III" — active, Forge Green, brand-story description, tagline, 2026, secret location.
- MBLAN24 / MBLAN25 — past editions, each a distinct color (demonstrates theming range).
- ~25 demo users incl. admin `bart_klumperman@live.nl` (role admin, pw `admin`).
- Games (existing 10-game seeder), full MBLAN26 schedule, blogs (incl. "Forged in the Barn"
  launch post), achievements, confirmed signups.
- `DatabaseSeeder` orchestrates; run via `php artisan migrate:fresh --seed`.

## Part F — Quality / refactor

- Remove hard-coded `orange`/`purple`/`pink` on landing; route through tokens.
- Extract repeated button/card/section markup into `<x-forge.*>`; delete mangled classes
  (e.g. `button-081458`) on the landing page.
- Fix `;;` and duplicated middleware nits in `routes/web.php`.

## Testing & go-live

- Pest: `ThemeService` ramp output + active-edition resolution + fallback; edition `color`
  persists via Filament form; every public page returns 200 with seeded data; auth pages render.
- Keep the existing Filament smoke tests green.
- Manual pass at `http://mblan.nl.test`.
- Assets: copy MBLAN26 logo PNG from `~/Downloads` into `public/images/`; keep `logo.svg` fallback.

## Build order

1. Theming engine (migration, model, ThemeService, tailwind vars, `<x-theme-vars>`, Filament picker).
2. Design system (fonts, tokens, `<x-forge.*>` components, utilities).
3. Page redesigns (landing → editions → blogs/games/achievements → auth → nav/layout).
4. Animations.
5. Seeders + fresh seed.
6. Tests + manual verification.
