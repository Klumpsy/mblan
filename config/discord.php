<?php

// config/discord.php
return [
    /*
    |--------------------------------------------------------------------------
    | Discord Webhook URL
    |--------------------------------------------------------------------------
    |
    | The webhook URL for your Discord channel. You can create this in your
    | Discord server settings under Integrations > Webhooks.
    |
    */
    'webhook_url' => env('DISCORD_WEBHOOK_URL', env('DISCORD_WEBHOOK_URL_LOBBY')),

    /*
    |--------------------------------------------------------------------------
    | News Webhook URL
    |--------------------------------------------------------------------------
    |
    | Optional separate webhook so news announcements land in their own
    | #nieuws channel. Create it in Discord under that channel's
    | Settings > Integrations > Webhooks. Falls back to the main webhook above
    | when unset, so news keeps posting to the default channel until you set it.
    |
    */
    'news_webhook_url' => env('DISCORD_NEWS_WEBHOOK_URL', env('DISCORD_WEBHOOK_URL', env('DISCORD_WEBHOOK_URL_LOBBY'))),

    /*
    |--------------------------------------------------------------------------
    | Queue Announcements
    |--------------------------------------------------------------------------
    |
    | Whether to queue Discord announcements or send them immediately.
    | Queueing is recommended for production to avoid blocking requests.
    |
    */
    // Default to sending synchronously so no queue worker is required.
    'queue_announcements' => env('DISCORD_QUEUE_ANNOUNCEMENTS', false),

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for webhook behavior
    |
    */
    'webhook_timeout' => env('DISCORD_WEBHOOK_TIMEOUT', 10), // seconds
    'webhook_retry_times' => env('DISCORD_WEBHOOK_RETRY_TIMES', 3),
    // Display name shown on webhook messages, regardless of the webhook's own name.
    'webhook_name' => env('DISCORD_WEBHOOK_NAME', 'Announcer bot'),

    /*
    |--------------------------------------------------------------------------
    | Bot / Application API
    |--------------------------------------------------------------------------
    |
    | Used by the richer integrations (Guild Scheduled Events, role sync,
    | slash commands and RSVP buttons). Every feature that needs one of these
    | is a silent no-op while the value is unset, so the app runs fine without
    | any of this configured.
    |
    */
    'bot_token' => env('DISCORD_BOT_TOKEN'),
    'application_id' => env('DISCORD_APPLICATION_ID'),
    'guild_id' => env('DISCORD_GUILD_ID'),
    // Ed25519 public key from the Discord app "General Information" page,
    // used to verify incoming interaction requests (slash commands, buttons).
    'public_key' => env('DISCORD_PUBLIC_KEY'),
    // Role handed to members when they log in with Discord.
    'member_role_id' => env('DISCORD_MEMBER_ROLE_ID'),
    // Channel the bot posts to (e.g. the RSVP message).
    'channel_id' => env('DISCORD_CHANNEL_ID'),
    // Physical location shown on the Discord scheduled events.
    'event_location' => env('DISCORD_EVENT_LOCATION', 'MBLAN26'),

    // How many minutes before a schedule item starts we post a reminder.
    'reminder_lead_minutes' => (int) env('DISCORD_REMINDER_LEAD_MINUTES', 15),
    // Per-game like counts that trigger a milestone announcement.
    'like_milestones' => [10, 25, 50, 100],
];

// Add these lines to your .env file:
/*
# Webhook announcements (already in use)
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your-webhook-url
DISCORD_QUEUE_ANNOUNCEMENTS=true
DISCORD_WEBHOOK_TIMEOUT=10
DISCORD_WEBHOOK_RETRY_TIMES=3
DISCORD_REMINDER_LEAD_MINUTES=15

# Bot / application (scheduled events, role sync, slash commands, RSVP)
DISCORD_BOT_TOKEN=
DISCORD_APPLICATION_ID=
DISCORD_GUILD_ID=
DISCORD_PUBLIC_KEY=
DISCORD_MEMBER_ROLE_ID=
DISCORD_CHANNEL_ID=
DISCORD_EVENT_LOCATION=MBLAN26
*/