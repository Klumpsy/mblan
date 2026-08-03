<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Edition>
 */
class EditionFactory extends Factory
{
    public function definition(): array
    {
        $year = fake()->unique()->numberBetween(2030, 2200);

        return [
            'name' => "MBLAN{$year}",
            'year' => $year,
            'slug' => "mblan{$year}",
            'is_active' => false,
            'primary_color' => fake()->hexColor(),
        ];
    }
}
