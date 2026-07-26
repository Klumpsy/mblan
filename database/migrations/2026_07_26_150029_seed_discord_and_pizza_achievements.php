<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A third wave: Discord activity, food orders, higher site tiers, and a
     * couple of admin-granted fun ones. Idempotent (keyed by slug).
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
            // --- Discord ---
            $auto('botfluisteraar', 'Botfluisteraar', 'Je eerste slash-commando in Discord gebruikt.', 'discord_commands', 1, 'tile_0122', '#5865f2'),
            $auto('commando-koning', 'Commando-koning', 'Vijfentwintig slash-commando\'s gebruikt.', 'discord_commands', 25, 'tile_0122', '#404eed'),
            // --- Eten bestellen ---
            $auto('pizzaliefhebber', 'Pizzaliefhebber', 'Een keer eten besteld via de site.', 'pizza_orders', 1, 'tile_0044', '#e5556b'),
            $auto('vaste-klant', 'Vaste klant', 'Bij drie rondes eten besteld.', 'pizza_orders', 3, 'tile_0047', '#c96a1f'),
            // --- Site (hogere tiers) ---
            $auto('superfan', 'Superfan', 'Vijfentwintig reacties ontvangen op je foto\'s.', 'likes_received', 25, 'tile_0018', '#e5556b'),
            $auto('gamefanaat', 'Gamefanaat', 'Twintig games geliket.', 'game_likes', 20, 'tile_0009', '#65e59a'),
            // --- Handmatig (admins) ---
            $manual('feestbeest', 'Feestbeest', 'Ging door tot de zon opkwam.', 'tile_0083', '#f6c667'),
            $manual('mvp-van-de-lan', 'MVP van de LAN', 'Uitgeroepen tot MVP van de editie.', 'tile_0096', '#f0a94b'),
        ];
    }
};
