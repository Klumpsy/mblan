<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Support\ThemeService;
use Illuminate\Database\Seeder;

class EditionSeeder extends Seeder
{
    public function run(): void
    {
        $editions = [
            [
                'name' => 'MBLAN24',
                'logo' => null,
                'description' => '<p>The first spark. MBLAN24 turned a quiet barn into a digital arena for a weekend of competitive chaos, questionable snacks and the friendships that started it all.</p>',
                'year' => 2024,
                'color' => '#F97316', // amber - where it all began
                'slug' => 'mblan24',
                'is_active' => false,
                'is_exclusive' => false,
            ],
            [
                'name' => 'MBLAN25',
                'logo' => null,
                'description' => '<p>The Barn II. Bigger screens, faster fibre and a legendary finals day. MBLAN25 raised the bar and set the stage for what was coming next.</p>',
                'year' => 2025,
                'color' => '#38BDF8', // cyan
                'slug' => 'mblan25',
                'is_active' => false,
                'is_exclusive' => false,
            ],
            [
                'name' => 'MBLAN26 - The Barn III',
                'logo' => 'editions/mblan26.jpg',
                'description' => '<p>Forged in the barn. High tech in a wooden barn - for one weekend friends gather in a digital smithy. No swords are hammered here, but friendships, inside jokes, victories and legendary memories are.</p><p><strong>This isn\'t just a LAN party. This is MBLAN.</strong></p>',
                'year' => 2026,
                'color' => ThemeService::DEFAULT_COLOR, // Forge Green #65E59A
                'slug' => 'mblan26',
                'is_active' => true,
                'is_exclusive' => false,
            ],
        ];

        foreach ($editions as $edition) {
            Edition::updateOrCreate(['slug' => $edition['slug']], $edition);
        }
    }
}
