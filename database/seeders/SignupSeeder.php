<?php

namespace Database\Seeders;

use App\Models\Signup;
use App\Models\User;
use Illuminate\Database\Seeder;

class SignupSeeder extends Seeder
{
    public function run(): void
    {
        User::inRandomOrder()->take(18)->get()->each(function (User $user) {
            Signup::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'stays_on_campsite' => (bool) random_int(0, 1),
                    'joins_barbecue' => (bool) random_int(0, 1),
                    'confirmed' => true,
                    'beer_count' => random_int(0, 32),
                    'wants_tshirt' => true,
                    'tshirt_size' => ['S', 'M', 'L', 'XL'][random_int(0, 3)],
                    'is_vegan' => random_int(0, 4) === 0,
                    'joins_pizza' => random_int(0, 9) !== 0,
                    'has_paid' => true,
                ]
            );
        });
    }
}
