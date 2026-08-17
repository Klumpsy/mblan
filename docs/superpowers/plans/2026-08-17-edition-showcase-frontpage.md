# Edition-themed Showcase Front Page — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the "Arti in Space" game at `/` with a cinematic, edition-themed showcase splash driven by the active `Edition`, and relocate the game to a public `/spel` route.

**Architecture:** A new standalone landing view (`landing.blade.php`) reads the active `Edition` (wordmark, tagline, palette, scenery sprites, countdown dates) with graceful fallbacks for every field. Layout is "cinematic title screen": a full-bleed themed backdrop (starfield + drifting scenery sprites keyed off `scenery_set`), with a bottom-anchored panel holding the wordmark/tagline, a full-lifecycle countdown, and a single "Doe mee" CTA that opens the existing auth modal. The game moves intact to `/spel`.

**Tech Stack:** Laravel (Blade `Route::view`), Livewire/Alpine (Alpine bundled by Livewire, registered in `resources/js/app.js`), Tailwind + Vite, Pest (feature + unit), Vitest (JS unit).

**Spec:** `docs/superpowers/specs/2026-08-17-edition-showcase-frontpage-design.md`

## Global Constraints

- **Dutch UI copy.** Sober, no decorative emoji.
- **Visual vocabulary only:** `frame-wood`, `btn-wood`, `clip-corner`, `font-display`, `font-pixel`, `.pixel`, `sprite-bob`, forge colors (`forge-black/steel`, `primary-*`). Do not introduce a new look.
- **Filament v5** — not touched here, but never type-hint `Filament\Forms\Form` (use `Filament\Schemas\Schema`). No admin changes in this plan.
- **No new DB columns, no new Filament fields.** Everything reads existing `Edition` fields.
- **DEPLOY MUST BUILD ASSETS** — after JS changes, prod needs `npm run build`. Local dev: `npm run dev` or `npm run build`.
- **Mobile-first** — the LAN crowd is on phones. Verify portrait widths.
- Space scenery images live at `public/images/scenery/space/*.png` (e.g. `astronaut.png`, `alien.png`, `planet_ring.png`, `planet_swirl.png`, `moon.png`, `rocket.png`, `ufo.png`, `satellite.png`, `comet.png`, `star_cluster.png`).

---

## File Structure

| File | Responsibility |
|------|----------------|
| `app/Models/Edition.php` | Add `countdownPhase()` — pure phase logic (none/upcoming/live/over) |
| `resources/js/edition-countdown.js` | Pure `countdownParts()` helper + `editionCountdown` Alpine factory |
| `resources/js/edition-countdown.test.js` | Vitest unit tests for `countdownParts()` |
| `resources/js/app.js` | Register `editionCountdown`; update the space-classic comment |
| `resources/views/spel.blade.php` | The relocated game (verbatim move of current `index.blade.php`) |
| `resources/views/landing.blade.php` | The showcase page (standalone HTML doc) |
| `resources/views/landing/_backdrop.blade.php` | Data-driven backdrop (starfield + planet + drifting sprites) |
| `resources/views/landing/_auth-modal.blade.php` | "Doe mee" auth modal (decoupled from game state) |
| `resources/views/index.blade.php` | **Deleted** (content lives on in `spel.blade.php`) |
| `resources/css/app.css` | Add showcase keyframes (`showcase-drift`, `showcase-float`, `showcase-twinkle`) |
| `routes/web.php` | `/` → `landing`; add public `/spel` → `spel` |
| `tests/Unit/EditionCountdownPhaseTest.php` | Unit tests for `countdownPhase()` |
| `tests/Feature/LandingPageTest.php` | Feature tests for `/` and `/spel` |

---

## Task 1: Edition countdown phase logic

**Files:**
- Modify: `app/Models/Edition.php`
- Test: `tests/Unit/EditionCountdownPhaseTest.php`

**Interfaces:**
- Consumes: `Edition` `starts_at` / `ends_at` (both cast to `date`, i.e. Carbon at midnight; may be null).
- Produces: `Edition::countdownPhase(): string` returning one of `'none'`, `'upcoming'`, `'live'`, `'over'`. `none` = no start date; `upcoming` = now before start; `live` = now within `[starts_at 00:00, ends_at 23:59:59]` (or any time at/after start when `ends_at` is null); `over` = now after the end day.

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/EditionCountdownPhaseTest.php`:

```php
<?php

use App\Models\Edition;
use Illuminate\Support\Carbon;

it('reports none when no start date is set', function () {
    $edition = new Edition(['starts_at' => null, 'ends_at' => null]);

    expect($edition->countdownPhase())->toBe('none');
});

it('reports upcoming before the start date', function () {
    Carbon::setTestNow('2026-08-01 12:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('upcoming');
});

it('reports live within the edition days, including the whole end day', function () {
    Carbon::setTestNow('2026-09-06 22:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('live');
});

it('reports live at or after start when no end date is set', function () {
    Carbon::setTestNow('2026-09-05 09:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => null]);

    expect($edition->countdownPhase())->toBe('live');
});

it('reports over after the end day', function () {
    Carbon::setTestNow('2026-09-07 00:30:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('over');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=EditionCountdownPhaseTest`
Expected: FAIL — `Call to undefined method App\Models\Edition::countdownPhase()`.

- [ ] **Step 3: Implement `countdownPhase()`**

In `app/Models/Edition.php`, add this method (place it just after `cssVariables()`, around line 89, keeping it with the other presentation helpers):

```php
    /**
     * Where the active edition sits in its lifecycle, for the landing
     * countdown: 'none' (no start date), 'upcoming' (before the start),
     * 'live' (during the LAN — the whole end day counts, or any time from
     * the start when there is no end date), or 'over' (after the end day).
     */
    public function countdownPhase(): string
    {
        if (! $this->starts_at) {
            return 'none';
        }

        $now = now();

        if ($now->lt($this->starts_at)) {
            return 'upcoming';
        }

        if (! $this->ends_at) {
            return 'live';
        }

        return $now->lte($this->ends_at->copy()->endOfDay()) ? 'live' : 'over';
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=EditionCountdownPhaseTest`
Expected: PASS (5 passing).

- [ ] **Step 5: Commit**

```bash
git add app/Models/Edition.php tests/Unit/EditionCountdownPhaseTest.php
git commit -m "Edition: countdownPhase() lifecycle helper for the landing countdown"
```

---

## Task 2: Relocate the game to `/spel`

Move the game off `/` to its own public route without changing the game itself. After this task both `/` (still the game) and `/spel` (the game) work; Task 4 flips `/` to the showcase and deletes `index.blade.php`.

**Files:**
- Create: `resources/views/spel.blade.php` (verbatim copy of `resources/views/index.blade.php`)
- Modify: `routes/web.php` (add the `/spel` route)
- Test: `tests/Feature/LandingPageTest.php`

**Interfaces:**
- Produces: named route `spel` (`route('spel')`) → renders the space game, public (no auth). The game's `spaceClassic` Alpine mount, sprite URLs, boss registry read, and `game.sync` config are unchanged.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/LandingPageTest.php`:

```php
<?php

use function Pest\Laravel\get;

it('serves the Arti in Space game at /spel', function () {
    get('/spel')
        ->assertOk()
        ->assertSee('spaceClassic', escape: false)
        ->assertSee('Arti en de boer, de ruimte in');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=LandingPageTest`
Expected: FAIL — 404 for `/spel` (route not defined).

- [ ] **Step 3: Create the `/spel` view**

Copy the current game page verbatim:

```bash
cp resources/views/index.blade.php resources/views/spel.blade.php
```

(No edits to the copy — it is the game exactly as it is today.)

- [ ] **Step 4: Add the route**

In `routes/web.php`, directly below the `/` route (currently line 20), add:

```php
// De editie-klassieker (Arti in Space). Publiek, net als vroeger op de
// landingspagina; game.sync blijft achter login.
Route::view('/spel', 'spel')->name('spel');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=LandingPageTest`
Expected: PASS (1 passing).

- [ ] **Step 6: Commit**

```bash
git add resources/views/spel.blade.php routes/web.php tests/Feature/LandingPageTest.php
git commit -m "Game: serve Arti in Space at its own public /spel route"
```

---

## Task 3: Countdown Alpine component

A pure `countdownParts()` helper (unit-tested) plus a thin `editionCountdown` Alpine factory that ticks every second. Registered in `app.js`.

**Files:**
- Create: `resources/js/edition-countdown.js`
- Create: `resources/js/edition-countdown.test.js`
- Modify: `resources/js/app.js`

**Interfaces:**
- Produces: `countdownParts(targetMs, nowMs)` → `{ days, hours, minutes, seconds, done }` (integers; `done` is `true` once `nowMs >= targetMs`; never returns negatives). `editionCountdown({ target })` → Alpine data object exposing reactive `days/hours/minutes/seconds/done`, seeded from `target` (an ISO 8601 string), ticking each second and stopping at zero.
- Consumes (in Task 4): the Blade countdown block mounts `x-data="editionCountdown({ target: '<starts_at ISO>' })"`.

- [ ] **Step 1: Write the failing test**

Create `resources/js/edition-countdown.test.js`:

```js
import { describe, it, expect } from 'vitest';
import { countdownParts } from './edition-countdown';

describe('countdownParts', () => {
  it('breaks a positive delta into days/hours/minutes/seconds', () => {
    const now = Date.UTC(2026, 8, 1, 0, 0, 0);
    const target = now + ((2 * 86400) + (3 * 3600) + (4 * 60) + 5) * 1000;
    expect(countdownParts(target, now)).toEqual({
      days: 2, hours: 3, minutes: 4, seconds: 5, done: false,
    });
  });

  it('clamps to zero and reports done at/after the target', () => {
    const now = Date.UTC(2026, 8, 1, 0, 0, 0);
    expect(countdownParts(now - 5000, now)).toEqual({
      days: 0, hours: 0, minutes: 0, seconds: 0, done: true,
    });
  });
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `npm test -- edition-countdown`
Expected: FAIL — cannot resolve `./edition-countdown` / `countdownParts` not exported.

- [ ] **Step 3: Implement the helper + component**

Create `resources/js/edition-countdown.js`:

```js
// Pure math: how much time is left, split into units. Deterministic — pass
// `nowMs` so it is unit-testable without touching the clock.
export function countdownParts(targetMs, nowMs) {
    const done = nowMs >= targetMs;
    let rest = Math.max(0, Math.floor((targetMs - nowMs) / 1000));

    const days = Math.floor(rest / 86400);
    rest -= days * 86400;
    const hours = Math.floor(rest / 3600);
    rest -= hours * 3600;
    const minutes = Math.floor(rest / 60);
    const seconds = rest - minutes * 60;

    return { days, hours, minutes, seconds, done };
}

// Alpine data: a live countdown to `target` (ISO 8601). Ticks each second and
// stops itself once the target passes.
export function editionCountdown({ target }) {
    return {
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        done: false,
        _timer: null,

        init() {
            const targetMs = new Date(target).getTime();

            const tick = () => {
                const p = countdownParts(targetMs, Date.now());
                this.days = p.days;
                this.hours = p.hours;
                this.minutes = p.minutes;
                this.seconds = p.seconds;
                this.done = p.done;

                if (p.done && this._timer) {
                    clearInterval(this._timer);
                    this._timer = null;
                }
            };

            tick();
            this._timer = setInterval(tick, 1000);
        },

        destroy() {
            if (this._timer) {
                clearInterval(this._timer);
            }
        },
    };
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `npm test -- edition-countdown`
Expected: PASS (2 passing).

- [ ] **Step 5: Register the Alpine component**

In `resources/js/app.js`:

Add the import near the other feature imports (after line 6, `import spaceClassic from './space-classic';`):

```js
import { editionCountdown } from './edition-countdown';
```

Inside the `alpine:init` listener, next to `Alpine.data('spaceClassic', spaceClassic);` (around line 65), add:

```js
    // editionCountdown: de aftelklok op de themische landingspagina.
    Alpine.data('editionCountdown', editionCountdown);
```

Also update the `spaceClassic` comment above line 65 — the game now lives on `/spel`, not the landing page. Change the comment text from "op de landingspagina" to "op /spel (Arti in Space)".

- [ ] **Step 6: Commit**

```bash
git add resources/js/edition-countdown.js resources/js/edition-countdown.test.js resources/js/app.js
git commit -m "Landing: editionCountdown Alpine component + pure countdownParts helper"
```

---

## Task 4: The showcase landing page

Build the cinematic showcase, point `/` at it, and delete the old `index.blade.php`. Uses the backdrop and auth-modal partials.

**Files:**
- Create: `resources/views/landing/_backdrop.blade.php`
- Create: `resources/views/landing/_auth-modal.blade.php`
- Create: `resources/views/landing.blade.php`
- Modify: `resources/css/app.css` (showcase keyframes)
- Modify: `routes/web.php` (`/` → `landing`)
- Delete: `resources/views/index.blade.php`
- Test: `tests/Feature/LandingPageTest.php` (extend)

**Interfaces:**
- Consumes: `Edition::current()`, `Edition::currentBrand()`, `$edition->cssVariables()` (via `<x-edition-theme />`), `$edition->scenerySprites()`, `$edition->sceneryLandmark()`, `$edition->countdownPhase()` (Task 1), `editionCountdown` Alpine (Task 3), routes `login`, `register`, `discord.redirect`, `schedule`, `spel` (Task 2), `editions.show`.
- Produces: named route `home` (`/`) rendering the showcase, public.

- [ ] **Step 1: Write the failing tests**

Extend `tests/Feature/LandingPageTest.php` — add these cases (keep the `/spel` test from Task 2):

```php
use App\Models\Edition;
use Illuminate\Support\Carbon;

it('renders the edition wordmark and the Doe mee CTA at /', function () {
    get('/')
        ->assertOk()
        ->assertSee('MBLAN')
        ->assertSee('Doe mee');
});

it('still renders / when there is no active edition', function () {
    Edition::query()->update(['is_active' => false]);

    get('/')
        ->assertOk()
        ->assertSee('Doe mee');
});

it('shows "Datum volgt" when the active edition has no start date', function () {
    Edition::current()->update(['starts_at' => null, 'ends_at' => null]);

    get('/')->assertSee('Datum volgt');
});

it('shows a live countdown when the edition is upcoming', function () {
    Carbon::setTestNow('2026-08-01');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('editionCountdown', escape: false);
});

it('shows a NU BEZIG badge during the edition', function () {
    Carbon::setTestNow('2026-09-05');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('NU BEZIG');
});

it('shows a recap link after the edition is over', function () {
    Carbon::setTestNow('2026-09-10');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('bekijk de recap');
});

it('links to the relocated game at /spel', function () {
    get('/')->assertSee(route('spel'));
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --filter=LandingPageTest`
Expected: the new cases FAIL — `/` still renders the game (no "Doe mee"), and `assertSee('Datum volgt')` etc. fail.

- [ ] **Step 3: Add the showcase keyframes**

In `resources/css/app.css`, append (near the existing `sprite-bob` keyframes, ~line 180):

```css
/* ===== Themed landing showcase ===== */
@keyframes showcase-drift {
    from { transform: translateX(-8%); }
    to   { transform: translateX(8%); }
}
@keyframes showcase-float {
    0%, 100% { transform: translateY(0); }
    50%      { transform: translateY(-14px); }
}
@keyframes showcase-twinkle {
    0%, 100% { opacity: 0.55; }
    50%      { opacity: 0.85; }
}
.showcase-stars { animation: showcase-twinkle 6s ease-in-out infinite; }
.showcase-planet { animation: showcase-float 12s ease-in-out infinite; will-change: transform; }
.showcase-sprite { animation: showcase-drift 14s ease-in-out infinite alternate; will-change: transform; }
```

- [ ] **Step 4: Create the backdrop partial**

Create `resources/views/landing/_backdrop.blade.php`. Data-driven: planet from `sceneryLandmark()`, drifting sprites from `scenerySprites()`, palette-tinted starfield behind. Degrades cleanly when `$edition` is null (starfield only).

```blade
@php
    $edition = \App\Models\Edition::current();
    $planet = $edition?->sceneryLandmark();
    // A handful of sprites to drift in the mid-layer; skip the planet itself.
    $drifters = collect($edition?->scenerySprites() ?? [])
        ->reject(fn ($url) => $planet && $url === $planet)
        ->take(6)
        ->values();
@endphp

<div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
    {{-- base gradient, tinted by the edition palette --}}
    <div class="absolute inset-0"
         style="background:
            radial-gradient(120% 90% at 50% 0%, color-mix(in srgb, var(--c-primary-700, #0f2417) 45%, #05080a) 0%, #05080a 60%),
            #05080a;"></div>

    {{-- starfield --}}
    <div class="showcase-stars absolute inset-0"
         style="background-image:
            radial-gradient(1px 1px at 20% 30%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 70% 15%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 40% 70%, #b7ffd6 40%, transparent),
            radial-gradient(1px 1px at 85% 55%, #cfefff 40%, transparent),
            radial-gradient(1px 1px at 55% 45%, #9fe6bd 40%, transparent),
            radial-gradient(1px 1px at 12% 82%, #eafff2 40%, transparent),
            radial-gradient(1px 1px at 33% 22%, #cfefff 40%, transparent);"></div>

    {{-- dominant landmark (planet), top-centre --}}
    @if ($planet)
        <img src="{{ $planet }}" alt=""
             class="pixel showcase-planet absolute left-1/2 -translate-x-1/2"
             style="top: -6vh; width: clamp(180px, 42vw, 360px);" />
    @endif

    {{-- drifting scenery sprites --}}
    @foreach ($drifters as $i => $url)
        <img src="{{ $url }}" alt=""
             class="pixel showcase-sprite absolute"
             style="
                top: {{ [18, 26, 40, 52, 34, 60][$i] ?? 30 }}%;
                {{ $i % 2 ? 'right' : 'left' }}: {{ [8, 14, 20, 12, 24, 6][$i] ?? 12 }}%;
                width: clamp(28px, {{ 4 + ($i % 3) }}vw, 64px);
                animation-duration: {{ 11 + $i * 2 }}s;
                animation-delay: -{{ $i * 3 }}s;
                opacity: 0.9;" />
    @endforeach

    {{-- bottom scrim so the text panel stays legible --}}
    <div class="absolute inset-x-0 bottom-0 h-2/3"
         style="background: linear-gradient(to top, rgba(4,12,8,0.94) 8%, rgba(4,12,8,0.55) 45%, transparent);"></div>
</div>
```

- [ ] **Step 5: Create the auth-modal partial**

Create `resources/views/landing/_auth-modal.blade.php`. This is the existing login modal, decoupled from game state (no score/wave lines). It expects to live inside an Alpine scope exposing `open` (provided by `landing.blade.php`).

```blade
@php([$brandBase, $brandAccent] = \App\Models\Edition::currentBrand())

<div x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-6">
    <div class="absolute inset-0 bg-forge-black/40" @click="open = false"></div>
    <div x-show="open" x-transition class="frame-wood relative w-full max-w-md p-8">
        <button type="button" @click="open = false" class="absolute right-3 top-3 font-pixel text-xs text-forge-steel/60 hover:text-primary-300">X</button>
        <div class="mb-1 font-pixel text-[8px] uppercase tracking-[0.2em] text-primary-400">De schuur is open</div>
        <h2 class="mb-6 font-display text-2xl font-bold uppercase tracking-wide text-white">Welkom bij {{ $brandBase }}@if ($brandAccent !== '')<span class="text-primary-400">{{ $brandAccent }}</span>@endif</h2>

        @auth
            <a href="{{ route('schedule') }}" class="btn-wood clip-corner w-full text-xs">Betreed De Schuur</a>
        @else
            <div x-data="{ showEmail: {{ $errors->any() ? 'true' : 'false' }} }">
                <a href="{{ route('discord.redirect') }}" class="btn-wood clip-corner block w-full text-center text-xs">Login met Discord</a>

                <button type="button" @click="showEmail = !showEmail"
                    class="mt-4 w-full text-center font-pixel text-[8px] uppercase tracking-widest text-forge-steel/50 hover:text-primary-300">
                    <span x-show="!showEmail">Inloggen met e-mail</span>
                    <span x-show="showEmail" x-cloak>Verberg e-mail login</span>
                </button>

                <div x-show="showEmail" x-cloak x-transition class="mt-4 border-t border-primary-500/15 pt-4">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf
                        <x-validation-errors />
                        <div>
                            <x-label for="email" value="E-mail" />
                            <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required />
                        </div>
                        <div>
                            <x-label for="password" value="Wachtwoord" />
                            <x-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
                        </div>
                        <label class="flex items-center">
                            <x-checkbox name="remember" />
                            <span class="ms-2 text-sm text-forge-steel/70">Onthoud mij</span>
                        </label>
                        <button type="submit" class="btn-wood clip-corner w-full text-xs">Inloggen</button>
                    </form>
                    <div class="mt-6 flex items-center justify-between font-pixel text-[8px] uppercase tracking-widest">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-forge-steel/60 hover:text-primary-300">Wachtwoord?</a>
                        @endif
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-primary-300 hover:text-primary-200">Registreren</a>
                        @endif
                    </div>
                </div>
            </div>
        @endauth
    </div>
</div>
```

- [ ] **Step 6: Create the landing page**

Create `resources/views/landing.blade.php`. Standalone HTML doc (same shell as the old `index.blade.php`), cinematic layout, full-lifecycle countdown.

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="{{ \App\Models\Edition::currentName() }}. High tech in een houten schuur, de Martin en Bart LAN party.">
    <title>{{ \App\Models\Edition::currentName() }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=chakra-petch:400,500,600,700|montserrat:400,500,600,700|press-start-2p:400&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-edition-theme />
    @livewireStyles
</head>

@php
    $edition = \App\Models\Edition::current();
    [$brandBase, $brandAccent] = \App\Models\Edition::currentBrand();
    $tagline = $edition?->tagline ?: 'Arti en de boer, de ruimte in';
    $phase = $edition?->countdownPhase() ?? 'none';
@endphp

<body class="font-sans antialiased bg-forge-black text-forge-steel overflow-hidden overscroll-none">
    <main x-data="{ open: false }"
          x-init="@if ($errors->any()) open = true @endif"
          class="relative min-h-dvh w-full overflow-hidden select-none">

        @include('landing._backdrop')

        {{-- ===== bottom-anchored content panel ===== --}}
        <div class="absolute inset-x-0 bottom-0 z-20 flex flex-col items-center gap-5 px-6 pb-10 pt-24 text-center sm:pb-14">

            {{-- wordmark / logo --}}
            @if ($edition?->logo_path)
                <img src="{{ asset('storage/'.$edition->logo_path) }}" alt="{{ \App\Models\Edition::currentName() }}"
                     class="pixel max-h-28 w-auto drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]" />
            @else
                <h1 class="flex items-baseline justify-center font-display font-bold leading-none tracking-tight">
                    <span class="bg-gradient-to-b from-white via-[#e7edeb] to-[#7f8f89] bg-clip-text text-transparent text-[clamp(2.2rem,10vw,5rem)] drop-shadow-[0_2px_8px_rgba(0,0,0,0.8)]">{{ $brandBase }}</span>
                    @if ($brandAccent !== '')
                        <span class="bg-gradient-to-b from-primary-200 via-primary-400 to-primary-600 bg-clip-text text-transparent text-[clamp(2.2rem,10vw,5rem)]">{{ $brandAccent }}</span>
                    @endif
                </h1>
            @endif

            <p class="font-pixel text-[9px] uppercase tracking-[0.18em] text-white/85 sm:text-[11px]">{{ $tagline }}</p>

            {{-- ===== full-lifecycle countdown ===== --}}
            <div class="min-h-[3.5rem]">
                @if ($phase === 'upcoming')
                    <div x-data="editionCountdown({ target: '{{ $edition->starts_at->toIso8601String() }}' })"
                         class="flex items-stretch gap-2 font-pixel">
                        @foreach ([['days','dg'], ['hours','u'], ['minutes','m'], ['seconds','s']] as [$unit, $label])
                            <div class="min-w-[3rem] rounded border border-primary-500/30 bg-forge-black/60 px-2 py-2">
                                <div class="text-lg font-bold leading-none text-primary-200"
                                     x-text="'{{ $unit }}' === 'days' ? String(days) : String({{ $unit }}).padStart(2, '0')"></div>
                                <div class="mt-1 text-[7px] uppercase tracking-widest text-forge-steel/60">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                @elseif ($phase === 'live')
                    <div class="inline-flex items-center gap-2 rounded border border-primary-500/40 bg-primary-500/10 px-4 py-3 font-pixel text-sm uppercase tracking-[0.2em] text-primary-200">
                        <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-primary-400"></span> NU BEZIG
                    </div>
                @elseif ($phase === 'over')
                    <p class="font-pixel text-[9px] uppercase tracking-widest text-forge-steel/70">
                        Tot ziens ·
                        <a href="{{ route('editions.show', $edition) }}" class="text-primary-300 hover:text-primary-200">bekijk de recap</a>
                    </p>
                @else
                    <p class="font-pixel text-[9px] uppercase tracking-widest text-forge-steel/60">Datum volgt</p>
                @endif
            </div>

            {{-- ===== single CTA ===== --}}
            @auth
                <a href="{{ route('schedule') }}" class="btn-wood clip-corner text-sm">Doe mee</a>
            @else
                <button type="button" @click="open = true" class="btn-wood clip-corner text-sm">Doe mee</button>
            @endauth

            <a href="{{ route('spel') }}" class="font-pixel text-[7px] uppercase tracking-widest text-forge-steel/45 hover:text-primary-300">Speel Arti in Space</a>
        </div>

        @include('landing._auth-modal')
    </main>

    @livewireScripts
</body>

</html>
```

- [ ] **Step 7: Point `/` at the landing view and delete the old game splash**

In `routes/web.php`, change the `/` route (line 20) from:

```php
Route::view('/', 'index')->name('home');
```

to:

```php
// Publieke themische landingspagina van de actieve editie.
Route::view('/', 'landing')->name('home');
```

Then delete the old view:

```bash
git rm resources/views/index.blade.php
```

- [ ] **Step 8: Run the full landing test file**

Run: `php artisan test --filter=LandingPageTest`
Expected: PASS (all 8 cases: `/spel` + the 7 new ones).

- [ ] **Step 9: Build assets and run the whole suite**

Run:
```bash
npm run build
php artisan test
npm test
```
Expected: Vite build succeeds; Pest suite green; Vitest green.

- [ ] **Step 10: Commit**

```bash
git add resources/views/landing.blade.php resources/views/landing/ resources/css/app.css routes/web.php tests/Feature/LandingPageTest.php
git rm resources/views/index.blade.php
git commit -m "Landing: edition-themed showcase at / (game moved to /spel)"
```

---

## Task 5: Browser verification

Not a code task — exercise the real UI per the project workflow (`verify-in-browser` skill). Unit/feature tests can't see the drifting sprites, the ticking countdown, or the modal.

**Files:** none (verification only).

- [ ] **Step 1: Serve the app**

Run: `php artisan serve --port=8123` (and `npm run dev` in a second shell, or rely on the `npm run build` bundle from Task 4).

- [ ] **Step 2: Drive `/` with Playwright (system Chrome, `channel: 'chrome'`)**

Verify and screenshot at a mobile portrait width (e.g. 390×844) and a desktop width (1280×800):
- Wordmark `MBLAN` + green accent renders; tagline shows.
- Backdrop: planet visible top-centre, scenery sprites drifting, starfield twinkling.
- Countdown: with the seeded active edition having no date, "Datum volgt" shows. Then, via tinker or admin, set `starts_at` to a near-future date and reload — the countdown ticks (seconds visibly change).
- "Doe mee" opens the auth modal (Discord + email toggle + register link).
- "Speel Arti in Space" navigates to `/spel` and the game boots (canvas renders, ship moves).

- [ ] **Step 3: Confirm the deploy build story**

Sanity-check that `npm run build` produced a fresh bundle (Alpine `editionCountdown` defined — no console error `editionCountdown is not defined`). This is the classic "works locally, broken on prod" trap; the countdown breaks silently if the bundle is stale. See memory `mblan-deploy-build-assets`.

- [ ] **Step 4: Report**

Attach the screenshots and note any visual polish follow-ups (e.g. sprite density, planet size on mobile). No commit unless a fix is needed.

---

## Self-Review

**Spec coverage:**
- Route swap + game relocation → Tasks 2 & 4. ✓
- Data-driven landing with fallbacks (wordmark/logo, tagline, palette, sprites) → Task 4 Step 6 + `_backdrop`. ✓
- Cinematic layout C (bottom-anchored panel, full-bleed backdrop) → Task 4. ✓
- Full-lifecycle countdown (none/upcoming/live/over) → Task 1 (logic) + Task 3 (ticking) + Task 4 (rendering). ✓
- "Doe mee" CTA reusing existing auth modal; logged-in → schedule → Task 4 `_auth-modal` + panel. ✓
- Backdrop keyed off `scenery_set`, palette-gradient fallback, farm deferred → `_backdrop` uses `sceneryLandmark()`/`scenerySprites()` (which resolve per `scenery_set`) over a palette-tinted gradient. ✓
- No new DB/Filament fields → confirmed, none added. ✓
- Testing (Pest feature + unit, browser verify) → Tasks 1, 2, 4, 5. ✓

**Placeholder scan:** No TBD/TODO; every code step has concrete content. ✓

**Type consistency:** `countdownPhase()` returns the four strings consumed in Task 4's `$phase` switch. `countdownParts()`/`editionCountdown({ target })` signatures match between Task 3 definition and Task 4 usage. `sceneryLandmark()`/`scenerySprites()` used as they exist in `Edition.php`. Route names (`spel`, `home`, `schedule`, `editions.show`, `discord.redirect`, `login`, `register`) all exist. ✓

**Note on the "over" recap link:** `route('editions.show', $edition)` is auth-gated and, for the active edition, redirects to the schedule — acceptable per spec ("schedule as today"). The "over" phase is a transient window before the next edition is made active.
