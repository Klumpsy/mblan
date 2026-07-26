<?php

namespace App\Console\Commands;

use App\Services\DiscordApiService;
use App\Support\DiscordCommands;
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

        // type 1 = CHAT_INPUT (slash command). The catalogue lives in one place
        // so /help and the registered commands can never drift apart.
        $commands = array_map(
            fn (array $command) => [...$command, 'type' => 1],
            DiscordCommands::all(),
        );

        $ok = $discord->registerGuildCommands($commands);
        $this->info($ok ? 'Slash commands geregistreerd.' : 'Registratie mislukt (zie logs).');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
