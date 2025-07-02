<?php

namespace Database\Factories;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EditionFactory extends Factory
{
    protected $model = Edition::class;

    public function definition()
    {
        $name = fake()->words(3, true) . ' Edition';
        return [
            'name' => $name,
            'logo' => null,
            'description' => fake()->paragraphs(3, true),
            'year' => fake()->year(),
            'slug' => Str::slug($name),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Edition $edition) {
            //Hier nog schedules koppelen eventueel
        });
    }
}
