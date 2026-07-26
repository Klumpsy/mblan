<?php

namespace App\Support;

/**
 * The bot's slash command catalogue: the single source of truth shared by
 * command registration (DiscordRegisterCommands) and the /help listing, so the
 * help text can never drift from what is actually registered with Discord.
 */
class DiscordCommands
{
    /**
     * @return array<int, array{name: string, description: string}>
     */
    public static function all(): array
    {
        return [
            ['name' => 'schema', 'description' => 'Toon het programma van vandaag'],
            ['name' => 'klassement', 'description' => 'Toon de standen van de actieve toernooien'],
            ['name' => 'volgende', 'description' => 'Wat staat er als eerstvolgende op het programma?'],
            ['name' => 'next', 'description' => 'Toon de eerstvolgende game met afbeelding en omschrijving'],
            ['name' => 'beer', 'description' => 'Noteer een biertje op jouw naam'],
            ['name' => 'beercount', 'description' => 'Toon het totaal aantal gedronken biertjes'],
            ['name' => 'beerlist', 'description' => 'Toon de bierranglijst van alle deelnemers'],
            ['name' => 'help', 'description' => 'Toon een overzicht van alle commando\'s'],
        ];
    }
}
