# MBLAN26 — project guide for Claude

MBLAN is a Dutch LAN-party site (tournaments, a photo timeline, news, a Discord
bot, and a pixel "Arti" farm mini-game on the landing page). Mobile-first.

## Stack

- **Laravel** (framework reports v13.x), **PHP 8**.
- **Filament v5** admin panel at `/admin` (v5 uses `Filament\Schemas\Schema`, not
  `Filament\Forms\Form` — a stray `Form` type-hint will 500 a form). Access is
  gated by `User::canAccessPanel()` → `role === 'admin'`.
- **Livewire v4** + **Alpine** (Alpine is bundled by Livewire; register custom
  components in the `alpine:init` listener in `resources/js/app.js`).
- **Tailwind** + **Vite**. **Pest** for tests.
- Prod: **Laravel Forge / DigitalOcean**. See memory `mblan-production-infra`.

## House style (important)

Dutch UI copy. Sober — **no decorative emoji** in Discord messages or general
copy (the emoji *reactions* feature is the deliberate exception). Visual
vocabulary: `clip-corner`, `metal-edge`, `frame-wood`, `btn-wood`, forge colors
(`forge-black/graphite/steel`, `primary-*` green `#37c26f`/`#65E59A`),
`font-display`, `font-pixel`, `.pixel` (pixelated rendering), `sprite-bob`
animation. Reuse these; don't introduce a new look.

## Key domains

- **Tournaments** (`app/Models/Tournament.php`, `TournamentResource` +
  `RelationManager/{UsersRelationManager,RegistrationsRelationManager}`).
  Players *register* (`registrations`) then get scored (`usersWithScores` pivot
  `tournament_user`: score, ranking, team_*). Scoring presets incl. `time`:
  `Tournament::isTimeBased()` stores score as **milliseconds** via
  `App\Support\TimeScore` (min/sec/ms input, `m:ss.mmm` display, lowest wins).
  Team maker (configurable size) can post the line-up to Discord.
- **Photo timeline** (`app/Livewire/Timeline/Feed.php`, `/tijdlijn`). Post/edit
  (own = owner, any = admin) via a modal that can change the photo; scroll chase
  (`timeline-chase.js`), lightbox (`timeline-lightbox.js`), reactions.
- **News** (`NewsController`, `resources/views/news`, `NewsResource`).
- **Reactions** — polymorphic ❤️/😂/🐐 via `HasReactions` trait +
  `<livewire:reactions :model>`. On timeline + news.
- **Discord** — `DiscordWebhookService` (outbound embeds; no-op without
  `discord.webhook_url`) and a bot: `DiscordInteractionController`
  (signed via `VerifyDiscordSignature`), slash commands from
  `App\Support\DiscordCommands`, registered by `discord:register-commands`.
- **Avatars** — no uploaded photo → stable random DiceBear pixel-art
  (`User::defaultProfilePhotoUrl()`); changeable on the profile form.
- **Image uploads** — phone photos are HEIC + large. Frontend forms convert
  HEIC→JPEG and downscale in the browser (`resources/js/image-upload.js`,
  Alpine `imageUpload`); Filament FileUploads use `imageResizeTargetWidth/Height`.
  Server-side `OptimizesImages` trait + `ImageOptimizer` downscale stored images.
  `public/.user.ini` raises PHP upload limits.

## Critical gotchas

- **DEPLOY MUST BUILD ASSETS.** "Works locally, broken on prod" is almost always
  the Forge deploy not running `npm ci && npm run build` (stale Vite bundle →
  Alpine components undefined → upload/edit/timeline break) or not deploying at
  all. Confirm deploy + build before assuming a code bug. See memory
  `mblan-deploy-build-assets`.
- **Worktrees** don't get `vendor/`/`node_modules/` (git-ignored). Either
  `composer install && npm install` in the worktree, or symlink from the main
  checkout (symlinking vendor can break Laravel path resolution — a real install
  is safer). Copy `.env` in too. The dev DB is shared MySQL `mblan`; run
  `php artisan migrate` in the worktree if a new table 500s locally.
- Prefer **in-repo fixes** over hand-editing the server (memory
  `mblan-prefer-in-app-fixes`).

## Workflow

- **TDD**: write a failing Pest test, implement, green. Feature tests use
  `RefreshDatabase` (sqlite :memory:). Filament: `Livewire::test(RelationManager,
  ['ownerRecord'=>$t,'pageClass'=>EditTournament::class])->callTableAction(...)`.
- **Browser-verify UI** with Playwright against a served app (`php artisan serve
  --port=8123`, drive with `playwright-core` + system Chrome `channel:'chrome'`).
  This caught the HEIC bug that unit tests missed — always verify uploads/JS UI
  in a real browser, and screenshot. Use `$CLAUDE_JOB_DIR/tmp` for scratch.
- Commit small, push to `master` when asked.
