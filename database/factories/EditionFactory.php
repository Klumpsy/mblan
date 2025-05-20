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
        $name = $this->faker->words(3, true) . ' Edition';
        return [
            'name' => $name,
            'logo' => null,
            'description' => $this->faker->paragraphs(3, true),
            'year' => $this->faker->year(),
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
