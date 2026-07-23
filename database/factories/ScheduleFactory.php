<?php

namespace Database\Factories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition()
    {
        return [
            'name' => 'Day ' . fake()->numberBetween(1, 5),
            'date' => now()->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function forDate($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        return $this->state(fn (array $attributes) => ['date' => $date]);
    }

    public function withGames($count = 3)
    {
        return $this->afterCreating(function (Schedule $schedule) use ($count) {
            if (class_exists(\App\Models\Game::class)) {
                $games = \App\Models\Game::factory()->count($count)->create();

                // Use schedule date if available, otherwise use current date
                $startTime = now()->setTime(9, 0);

                foreach ($games as $game) {
                    $endTime = (clone $startTime)->addMinutes(
                        fake()->randomElement([30, 45, 60, 90, 120])
                    );

                    $schedule->games()->attach($game, [
                        'start_date' => $startTime,
                        'end_date' => $endTime,
                    ]);

                    $startTime = (clone $endTime)->addMinutes(15);
                }
            }
        });
    }

    protected function columnExists($table, $column)
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Exception $e) {
            return false;
        }
    }
}
