<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence;

        return [
            'title' => $title,
            'author_id' => User::factory(),
            'image' => null,
            'content' => '<p>'.fake()->paragraph.'</p>',
            'preview_text' => fake()->text(120),
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'published' => true,
            'published_at' => now(),
        ];
    }
}
