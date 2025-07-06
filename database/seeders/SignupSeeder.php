<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Signup;
use Illuminate\Database\Seeder;

class SignupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $edition25 = Edition::where('slug', 'mblan25')->first();
        Signup::factory()->count(20)->create([
            'stays_on_campsite' => true,
            'joins_barbecue' => true,
            'edition_id' => $edition25->id,
            'wants_tshirt' => true,
            'tshirt_text' => 'MBLAN 25',
            'tshirt_size' => 'M',
            'is_vegan' => false,
            'joins_pizza' => true,
            'confirmed' => true,
        ]);
    }
}
