<?php

namespace App\Console\Commands;

use App\Services\DiscordApiService;
use Illuminate\Console\Command;

/**
 * Posts an RSVP message to the configured Discord channel with "Ik kom" and
 * "Ik kom niet" buttons. Button presses are handled by the interactions
 * endpoint. Run when you want to (re)open the RSVP.
 */
class DiscordPostRsvp extends Command
{
    protected $signature = 'discord:post-rsvp {message? : Optional custom message}';
    protected $description = 'Post an RSVP message with buttons to Discord';

    public function handle(DiscordApiService $discord): int
    {
        $channelId = config('discord.channel_id');

        if (! $discord->enabled() || empty($channelId)) {
            $this->warn('Discord bot / kanaal niet geconfigureerd. Overgeslagen.');

            return self::SUCCESS;
        }

        $message = $this->argument('message')
            ?: 'Kom je naar MBLAN26? Laat het weten.';

        // One action row with two buttons. Styles: 3 = success (green), 4 = danger (red).
        $components = [[
            'type' => 1,
            'components' => [
                ['type' => 2, 'style' => 3, 'label' => 'Ik kom', 'custom_id' => 'rsvp:yes'],
                ['type' => 2, 'style' => 4, 'label' => 'Ik kom niet', 'custom_id' => 'rsvp:no'],
            ],
        ]];

        $posted = $discord->postMessage($channelId, $message, $components);
        $this->info($posted ? 'RSVP-bericht geplaatst.' : 'Plaatsen mislukt (zie logs).');

        return $posted ? self::SUCCESS : self::FAILURE;
    }
}
