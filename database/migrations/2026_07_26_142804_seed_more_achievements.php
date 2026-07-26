<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A second wave of (mostly automatic) achievements: signups, camping, bbq,
     * shirt, editions and a full spread of tournament + beer/photo/reaction
     * tiers. Idempotent (keyed by slug). Admins can still add more in Filament.
     */
    public function up(): void
    {
        foreach ($this->catalogue() as $row) {
            $existing = DB::table('achievements')->where('slug', $row['slug'])->first();

            if ($existing) {
                DB::table('achievements')->where('slug', $row['slug'])->update($row + ['updated_at' => now()]);
            } else {
                DB::table('achievements')->insert($row + ['created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('achievements')->whereIn('slug', array_column($this->catalogue(), 'slug'))->delete();
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
            // --- Aanmelding / kamp / eten ---
            $auto('ingeschreven', 'Ingeschreven', 'Aangemeld voor MBLAN26.', 'signups', 1, 'tile_0109', '#65e59a'),
            $auto('kampeerder', 'Kampeerder', 'Blijft een keer slapen op de camping.', 'camping', 1, 'tile_0015', '#8aebb2'),
            $auto('bbq-ganger', 'BBQ-ganger', 'Sluit aan bij de barbecue.', 'barbecue', 1, 'tile_0032', '#f0a94b'),
            $auto('eigen-shirt', 'Eigen shirt', 'Besteld een eigen MBLAN-shirt.', 'tshirt', 1, 'tile_0076', '#4ec9d4'),
            // Editions are a single event now, so "veteraan" is admin-granted.
            $manual('trouwe-bezoeker', 'Trouwe bezoeker', 'Al vaker van de partij geweest.', 'tile_0096', '#c96a1f'),
            // --- Toernooien ---
            $auto('toernooibeest', 'Toernooibeest', 'Aangemeld voor drie toernooien.', 'tournament_signups', 3, 'tile_0083', '#f6c667'),
            $auto('toernooiwinnaar', 'Toernooiwinnaar', 'Een toernooi gewonnen.', 'tournaments_won', 1, 'tile_0044', '#e5556b'),
            $auto('seriewinnaar', 'Seriewinnaar', 'Drie toernooien gewonnen.', 'tournaments_won', 3, 'tile_0047', '#e08a2f'),
            $auto('podiumbeest', 'Podiumbeest', 'Op het podium geëindigd (top 3).', 'tournament_podiums', 1, 'tile_0027', '#8aebb2'),
            $auto('toernooiveteraan', 'Toernooiveteraan', 'Aan drie toernooien meegedaan.', 'tournaments_played', 3, 'tile_0120', '#65e59a'),
            // --- Bier (hogere tiers) ---
            $auto('tienklapper', 'Tienklapper', 'Tien biertjes gedronken.', 'beer', 10, 'tile_0072', '#f0a94b'),
            $auto('vatvernietiger', 'Vatvernietiger', 'Twintig biertjes gedronken.', 'beer', 20, 'tile_0085', '#e08a2f'),
            $auto('biermonument', 'Biermonument', 'Veertig biertjes. Een wandelend standbeeld.', 'beer', 40, 'tile_0085', '#c96a1f'),
            // --- Site (hogere tiers) ---
            $auto('fotolegende', 'Fotolegende', 'Vijfentwintig foto\'s op de tijdlijn.', 'photos', 25, 'tile_0088', '#4ec9d4'),
            $auto('reactiekoning', 'Reactiekoning', 'Vijfentwintig reacties gegeven.', 'reactions_given', 25, 'tile_0122', '#f6c667'),
        ];
    }
};
