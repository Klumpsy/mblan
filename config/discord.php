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
    'webhook_url' => env('DISCORD_WEBHOOK_URL_LOBBY'),

    /*
    |--------------------------------------------------------------------------
    | Queue Announcements
    |--------------------------------------------------------------------------
    |
    | Whether to queue Discord announcements or send them immediately.
    | Queueing is recommended for production to avoid blocking requests.
    |
    */
    'queue_announcements' => env('DISCORD_QUEUE_ANNOUNCEMENTS', true),

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
];

// Add these lines to your .env file:
/*
DISCORD_WEBHOOK_URL=https://discord.com/api/webhooks/your-webhook-url
DISCORD_QUEUE_ANNOUNCEMENTS=true
DISCORD_ANNOUNCE_CREATION=false
DISCORD_WEBHOOK_TIMEOUT=10
DISCORD_WEBHOOK_RETRY_TIMES=3
*/