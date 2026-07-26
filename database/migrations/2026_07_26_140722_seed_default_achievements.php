<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed a robust default catalogue of achievements. Idempotent (keyed by
     * slug) so it is safe to re-run and never clobbers admin edits to rows that
     * already exist. Admins can add/edit more in the Filament backend.
     *
     * Automatic ones carry a `metric` + `threshold`; manual ones (edition
     * attendance) are granted by admins for backfill.
     */
    public function up(): void
    {
        foreach ($this->catalogue() as $row) {
            $existing = DB::table('achievements')->where('slug', $row['slug'])->first();

            if ($existing) {
                DB::table('achievements')->where('slug', $row['slug'])
                    ->update($row + ['updated_at' => now()]);
            } else {
                DB::table('achievements')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('achievements')
            ->whereIn('slug', array_column($this->catalogue(), 'slug'))
            ->delete();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function catalogue(): array
    {
        $auto = fn (string $slug, string $name, string $desc, string $metric, int $threshold, string $tile, string $color) => [
            'slug' => $slug, 'name' => $name, 'description' => $desc,
            'type' => 'automatic', 'metric' => $metric, 'threshold' => $threshold,
            'icon_path' => "images/farm/{$tile}.png", 'color' => $color, 'grayed_color' => '#3b4a42',
        ];
        $manual = fn (string $slug, string $name, string $desc, string $tile, string $color) => [
            'slug' => $slug, 'name' => $name, 'description' => $desc,
            'type' => 'manual', 'metric' => null, 'threshold' => null,
            'icon_path' => "images/farm/{$tile}.png", 'color' => $color, 'grayed_color' => '#3b4a42',
        ];

        return [
            // --- Bier (Discord /beer command) ---
            $auto('eerste-biertje', 'Eerste biertje', 'Je eerste biertje genoteerd via Discord.', 'beer', 1, 'tile_0072', '#f6c667'),
            $auto('dorstige-smid', 'Dorstige smid', 'Vijf biertjes gedronken.', 'beer', 5, 'tile_0072', '#f0a94b'),
            $auto('biersmid', 'Biersmid', 'Vijftien biertjes gedronken.', 'beer', 15, 'tile_0073', '#e08a2f'),
            $auto('bar-legende', 'Barlegende', 'Dertig biertjes. Respect en zorg.', 'beer', 30, 'tile_0085', '#c96a1f'),
            // --- Site: timeline / reacties / games ---
            $auto('eerste-foto', 'Eerste foto', 'Je eerste foto op de tijdlijn geplaatst.', 'photos', 1, 'tile_0088', '#65e59a'),
            $auto('hoffotograaf', 'Hoffotograaf', 'Tien foto\'s gedeeld op de tijdlijn.', 'photos', 10, 'tile_0088', '#4ec9d4'),
            $auto('publiekslieveling', 'Publiekslieveling', 'Tien reacties ontvangen op je foto\'s.', 'likes_received', 10, 'tile_0044', '#e5556b'),
            $auto('fanatieke-reageerder', 'Fanatieke reageerder', 'Tien reacties achtergelaten bij anderen.', 'reactions_given', 10, 'tile_0122', '#f0a94b'),
            $auto('gamekenner', 'Gamekenner', 'Vijf games geliket.', 'game_likes', 5, 'tile_0009', '#65e59a'),
            // --- Arti game + toernooien ---
            $auto('schuurheld', 'Schuurheld', 'De schuur bereikt in Het Arti Spel.', 'barn_completed', 1, 'tile_0027', '#8aebb2'),
            $auto('toernooiganger', 'Toernooiganger', 'Aangemeld voor minstens één toernooi.', 'tournament_signups', 1, 'tile_0083', '#f6c667'),
            // --- Discord ---
            $auto('discord-gekoppeld', 'Discord gekoppeld', 'Je Discord-account gekoppeld aan MBLAN26.', 'discord_linked', 1, 'tile_0122', '#5865f2'),
            // --- Manual / backfill (admins grant these) ---
            $manual('editie-2024', 'Editie 2024', 'Was erbij op MBLAN 2024.', 'tile_0096', '#c96a1f'),
            $manual('editie-2025', 'Editie 2025', 'Was erbij op MBLAN 2025.', 'tile_0096', '#e08a2f'),
            $manual('mblan-veteraan', 'MBLAN-veteraan', 'Een trouwe deelnemer van het eerste uur.', 'tile_0015', '#8aebb2'),
        ];
    }
};
