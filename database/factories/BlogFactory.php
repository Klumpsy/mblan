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
            'title' => fake()->sentence,
            'author_id' => User::factory(),
            'image' => null,
            'content' => fake()->paragraph,
            'preview_text' => fake()->text(100),
            'slug' => fake()->unique()->slug,
            'published' => true,
            'published_at' => now(),
        ];
    }
}
