<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'year_of_release' => fake()->year(),
            'text_block_one' => fake()->paragraph(4),
            'text_block_two' => fake()->paragraph(3),
            'text_block_three' => fake()->paragraph(2),
            'short_description' => fake()->sentence(),
            'image' => fake()->imageUrl(640, 480, 'games'),
            'link_to_website' => fake()->url(),
            'link_to_youtube' => 'https://www.youtube.com/watch?v=' . fake()->regexify('[A-Za-z0-9_-]{11}'),
            'likes' => 0,
        ];
    }
}
