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
            'stays_on_campsite' => $this->faker->boolean(),
            'joins_barbecue' => $this->faker->boolean(),
            'user_id' => User::factory(),          
            'edition_id' => Edition::factory(),
            'confirmed' => $this->faker->boolean(70),
        ];
    }
}
