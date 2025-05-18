<?php

namespace Database\Seeders;

use App\Models\Edition;
use Illuminate\Database\Seeder;

class EditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $editions = [
            [
                'name' => 'MBLAN24',
                'logo' => 'editions/mblan24.png',
                'description' => 'The 2024 edition of MBLAN featuring competitive tournaments across multiple game titles. Join us for an unforgettable gaming experience with top players from around the region.',
                'year' => 2024,
                'slug' => 'mblan24'
            ],
            [
                'name' => 'MBLAN25',
                'logo' => 'editions/mblan25.svg',
                'description' => 'The 2025 edition of MBLAN promises to be bigger and better than ever. With expanded game offerings, improved facilities, and increased prize pools, MBLAN25 is set to be a landmark event in competitive gaming.',
                'year' => 2025,
                'slug' => 'mblan25'
            ]
        ];

        foreach ($editions as $edition) {
            Edition::create($edition);
        }
    }
}
