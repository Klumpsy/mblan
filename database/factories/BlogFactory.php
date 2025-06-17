<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'author_id' => User::factory(),
            'image' => null,
            'content' => $this->faker->paragraph,
            'preview_text' => $this->faker->text(100),
            'slug' => $this->faker->unique()->slug,
            'published' => true,
            'published_at' => now(),
        ];
    }
}
