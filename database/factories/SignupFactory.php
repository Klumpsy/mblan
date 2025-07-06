<?php

namespace Database\Factories;

use App\Models\Signup;
use App\Models\User;
use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;

class SignupFactory extends Factory
{
    protected $model = Signup::class;

    public function definition(): array
    {
        return [
            'stays_on_campsite' => fake()->boolean(),
            'joins_barbecue' => fake()->boolean(),
            'user_id' => User::factory(),
            'edition_id' => Edition::factory(),
            'wants_tshirt' => fake()->boolean(),
            'tshirt_text' => fake()->optional(0.5)->text(20),
            'tshirt_size' => fake()->randomElement(['S', 'M', 'L']),
            'is_vegan' => fake()->boolean(20),
            'joins_pizza' => fake()->boolean(90),
            'confirmed' => fake()->boolean(70),
        ];
    }
}
