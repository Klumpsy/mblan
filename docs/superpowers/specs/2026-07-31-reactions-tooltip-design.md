# Wie heeft gereageerd — tooltip op reactions + Discord HTML-sanitizing

Date: 2026-07-31
Status: approved

## 1. Reactions "who reacted" tooltip

Users can't see *who* reacted (❤️/😂/🐐) on news items and timeline photos —
only counts. On hover (desktop) or long-press (mobile) each reaction button
shows a small tooltip listing the names of the users who reacted.

### Data

- `Reactions::render()` fetches reactor names in one extra query
  (`reactions` joined to `users`, ordered oldest reaction first), grouped by
  emoji, and passes `names` to the view.
- Cap at 15 names per emoji; more become `+N anderen`.
- No new endpoints, no lazy loading: names render into the DOM server-side
  and refresh automatically on every Livewire re-render (so toggling your own
  reaction updates the list).

### UI (Blade + Alpine, house style)

- Tooltip: small dark panel (`forge-graphite` bg, `clip-corner`, thin
  `primary-500` border), positioned above the button, one name per line,
  Dutch copy. No tooltip when the count is 0.
- Desktop: show on `mouseenter` after ~150ms, hide on `mouseleave`. Also on
  `focus`/`blur` for keyboard users.
- Mobile: `touchstart` starts a 500ms timer; when it fires the tooltip shows
  and the subsequent `click` is suppressed (long-press must NOT toggle the
  reaction). A short tap toggles exactly as today. Button gets `select-none`
  and `-webkit-touch-callout: none` so iOS doesn't fight the long-press.
  Touching elsewhere closes the tooltip.

### Not doing (YAGNI)

No modal with the full list, no avatars, no per-emoji tabs, no schema changes.

## 2. Discord embeds: strip HTML

Discord messages sometimes contain literal `<p>` tags. Rich-text content
(news `content`, and any HTML that ends up in text fields) reaches
`DiscordWebhookService::sendEmbed()` unsanitized except for one ad-hoc
`strip_tags` in `announceNews()`.

Fix centrally in `sendEmbed()`: for the title, description and every field
value — convert `<br>` and paragraph boundaries to newlines, `strip_tags`,
decode HTML entities, and collapse excess blank lines. Markdown (e.g.
`**Team**`) is untouched. All webhook messages are covered regardless of
which caller forgot to sanitize.

## Testing

- Pest: rendered Reactions component contains reactor names, respects the
  15-name cap with `+N anderen`, shows nothing for 0 reactions.
- Pest: `DiscordWebhookService` (via `Http::fake()`) sends embeds whose
  description/fields contain no HTML tags and preserve paragraph breaks.
- Browser-verify hover + long-press with Playwright per project workflow.
