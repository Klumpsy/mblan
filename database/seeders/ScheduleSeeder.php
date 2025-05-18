<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Game;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $edition24 = Edition::where('slug', 'mblan24')->first();
        $edition25 = Edition::where('slug', 'mblan25')->first();

        if (!$edition24 || !$edition25) {
            $this->command->error("Editions not found! Make sure to run the EditionSeeder first.");
            return;
        }

        $games = Game::all();

        if ($games->isEmpty()) {
            $this->command->error("No games found! Make sure to run the GameSeeder first.");
            return;
        }

        $getGameByName = function ($name) use ($games) {
            $game = $games->where('name', $name)->first();
            if (!$game) {
                $this->command->warn("Game '$name' not found, skipping...");
            }
            return $game;
        };

        $mblan24Schedules = [
            [
                'name' => 'Day 1 - Opening',
                'edition_id' => $edition24->id,
                'date' => '2024-06-10',
                'games' => [
                    $getGameByName('Warcraft III'),
                    $getGameByName('Hearthstone - Battlegrounds')
                ]
            ],
            [
                'name' => 'Day 2 - Main Event',
                'edition_id' => $edition24->id,
                'date' => '2024-06-11',
                'games' => [
                    $getGameByName('Team Fortress 2'),
                    $getGameByName('Age of Empires III'),
                    $getGameByName('Among Us')
                ]
            ],
            [
                'name' => 'Day 3 - Finals',
                'edition_id' => $edition24->id,
                'date' => '2024-06-12',
                'games' => [
                    $getGameByName('Diablo III'),
                    $getGameByName('Unreal Tournament 2004')
                ]
            ]
        ];

        $mblan25Schedules = [
            [
                'name' => 'Day 1 - Opening Ceremony',
                'edition_id' => $edition25->id,
                'date' => '2025-07-15',
                'games' => [
                    $getGameByName('Battlefield Vietnam'),
                    $getGameByName('Trackmania')
                ]
            ],
            [
                'name' => 'Day 2 - Main Tournament',
                'edition_id' => $edition25->id,
                'date' => '2025-07-16',
                'games' => [
                    $getGameByName('Warcraft III'),
                    $getGameByName('Team Fortress 2'),
                    $getGameByName('Mario Party')
                ]
            ],
            [
                'name' => 'Day 3 - Finals Day',
                'edition_id' => $edition25->id,
                'date' => '2025-07-17',
                'games' => [
                    $getGameByName('Age of Empires III'),
                    $getGameByName('Among Us'),
                    $getGameByName('Hearthstone - Battlegrounds')
                ]
            ]
        ];

        $createSchedules = function ($scheduleData) {
            foreach ($scheduleData as $data) {
                $games = $data['games'];
                unset($data['games']);

                $schedule = Schedule::create($data);
                $startHour = 10;

                foreach ($games as $game) {
                    if ($game) {
                        $endHour = $startHour + 3;

                        $schedule->games()->attach($game->id, [
                            'start_date' => Carbon::parse($data['date'])->setTime($startHour, 0, 0),
                            'end_date' => Carbon::parse($data['date'])->setTime($endHour, 0, 0),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        $startHour = $endHour + 1;
                    }
                }
            }
        };

        $createSchedules($mblan24Schedules);
        $createSchedules($mblan25Schedules);

        $this->command->info('Schedules created successfully!');
    }
}
