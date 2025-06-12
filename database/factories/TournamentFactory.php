<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\Schedule;
use App\Models\Tournament;
use Illuminate\Database\Eloquent\Factories\Factory;

class TournamentFactory extends Factory
{
    protected $model = Tournament::class;

    public function definition(): array
    {
        $startTime = $this->faker->time('H:i:s');
        $startDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
        $endDateTime = $startDateTime->copy()->addMinutes($this->faker->numberBetween(30, 300));

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'is_active' => $this->faker->boolean(80),
            'time_start' => $startTime,
            'time_end' => $endDateTime->format('H:i:s'),
            'game_id' => Game::factory(),
            'schedule_id' => Schedule::factory(),
        ];
    }
}
