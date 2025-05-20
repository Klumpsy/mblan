<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition()
    {
        $attributes = [
            'name' => 'Day ' . $this->faker->numberBetween(1, 5),
            'edition_id' => Edition::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($this->columnExists('schedules', 'date')) {
            $attributes['date'] = now()->format('Y-m-d');
        }

        return $attributes;
    }

    public function forDate($date)
    {
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        if ($this->columnExists('schedules', 'date')) {
            return $this->state(function (array $attributes) use ($date) {
                return [
                    'date' => $date,
                ];
            });
        }

        return $this;
    }

    public function forEdition($edition)
    {
        $editionId = $edition instanceof Edition ? $edition->id : $edition;

        return $this->state(function (array $attributes) use ($editionId) {
            return [
                'edition_id' => $editionId,
            ];
        });
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
                        $this->faker->randomElement([30, 45, 60, 90, 120])
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
