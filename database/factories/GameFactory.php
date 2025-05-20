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
            'name' => $this->faker->words(3, true),
            'year_of_release' => $this->faker->year(),
            'text_block_one' => $this->faker->paragraph(4),
            'text_block_two' => $this->faker->paragraph(3),
            'text_block_three' => $this->faker->paragraph(2),
            'short_description' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl(640, 480, 'games'),
            'link_to_website' => $this->faker->url(),
            'link_to_youtube' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'likes' => 0,
        ];
    }
}
