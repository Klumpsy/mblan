---
name: verify-in-browser
description: Verify an MBLAN UI/JS change by serving the app and driving it in a real browser with Playwright. Use for uploads, Livewire/Alpine interactions, Filament panel, lightbox/modals — anything Pest can't exercise. Especially for image uploads (HEIC) and mobile behaviour.
---

# Verify MBLAN changes in a real browser

Pest covers backend logic, but JS/Livewire/Alpine and image handling must be
seen running. This recipe caught the HEIC upload bug that unit tests missed.

## Recipe

1. **Build + serve** (in your worktree, deps installed):
   ```bash
   npm run build
   php artisan serve --host=127.0.0.1 --port=8123 &   # run in background
   ```
2. **Seed data** via a bootstrapped PHP script (NOT `php artisan tinker <file>`,
   which hangs). Create a verified user, e.g.:
   ```php
   <?php
   require '<worktree>/vendor/autoload.php';
   $app = require '<worktree>/bootstrap/app.php';
   $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
   use App\Models\User;
   $u = User::firstOrNew(['email' => 'verify@example.test']);
   $u->forceFill(['name'=>'Test','password'=>bcrypt('password123'),
       'email_verified_at'=>now(),'role'=>'admin'])->save();
   ```
   `forceFill` — `email_verified_at`/`role`/`password` aren't mass-assignable.
   If a table 500s locally, `php artisan migrate` (dev DB is shared MySQL).
3. **Drive with Playwright** using `playwright-core` + the system Chrome (no
   browser download): install once in a scratch dir under `$CLAUDE_JOB_DIR/tmp`,
   `chromium.launch({ channel: 'chrome', headless: true })`. Log in at `/login`
   (`#email`, `#password`), then exercise the change and `page.screenshot()`.
4. **Read the screenshot** to confirm it looks right; capture booleans for the
   behaviour (e.g. `modal_visible`, `story after next()`), and check
   `page.on('pageerror')` is empty.

## Notes

- HEIC: make a test file with `sips -s format heic in.jpg --out out.heic`.
  Chrome can't decode HEIC (Safari can); heic2any converts it in-browser.
- Livewire v4 upload endpoint is obfuscated (`/livewire-xxxx/upload-file`) —
  match `upload-file`, not `/livewire/upload-file`.
- Transient overlays (Arti chase, upload spinner): start `waitForSelector`
  *before* the trigger, or they vanish before you screenshot.
- Clean up throwaway users/photos and stop the server when done.
- **Filament panel navigation:** `page.goto('/admin/...')` gets `ERR_ABORTED`
  (Livewire SPA-mode). Log in at `/admin/login`, then navigate by clicking the
  sidebar links. To open a record, use the table search box and click
  `tr:has-text("Name") >> text=Name` — a bare `text=Name` hits the
  "Zoekopdracht" filter chip instead of the row.
- **Env overrides don't reach the server:** `artisan serve` whitelists which
  env vars pass to its PHP workers, so `DISCORD_PUBLIC_KEY=x php artisan serve`
  silently drops the var (plain `php -S` + server.php has the same problem via
  variables_order). To test config-driven behaviour like the Discord signature
  middleware: back up `.env`, append the var, serve, and restore `.env` after.
