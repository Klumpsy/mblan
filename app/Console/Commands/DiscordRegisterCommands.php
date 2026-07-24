<?php

namespace App\Console\Commands;

use App\Services\DiscordApiService;
use Illuminate\Console\Command;

/**
 * Registers the guild slash commands with Discord. Run once after configuring
 * the bot, and again whenever the command list below changes. Guild commands
 * update instantly (global ones can take up to an hour).
 */
class DiscordRegisterCommands extends Command
{
    protected $signature = 'discord:register-commands';
    protected $description = 'Register the MBLAN slash commands with Discord';

    public function handle(DiscordApiService $discord): int
    {
        if (! $discord->enabled() || ! config('discord.application_id')) {
            $this->warn('Discord bot niet volledig geconfigureerd. Overgeslagen.');

            return self::SUCCESS;
        }

        $commands = [
            ['name' => 'schema', 'description' => 'Toon het programma van vandaag', 'type' => 1],
            ['name' => 'klassement', 'description' => 'Toon de standen van de actieve toernooien', 'type' => 1],
            ['name' => 'volgende', 'description' => 'Wat staat er als eerstvolgende op het programma?', 'type' => 1],
        ];

        $ok = $discord->registerGuildCommands($commands);
        $this->info($ok ? 'Slash commands geregistreerd.' : 'Registratie mislukt (zie logs).');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
