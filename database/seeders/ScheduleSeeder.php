<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $games = Game::all();
        if ($games->isEmpty()) {
            $this->command->error('No games found! Run GameSeeder first.');
            return;
        }

        $game = fn ($name) => $games->firstWhere('name', $name);

        $days = [
            ['name' => 'Day 1 - Ignition', 'date' => '2026-07-10', 'games' => ['Trackmania', 'Team Fortress 2']],
            ['name' => 'Day 2 - The Anvil', 'date' => '2026-07-11', 'games' => ['Warcraft III', 'Age of Empires III', 'Among Us']],
            ['name' => 'Day 3 - Tempered Steel (Finals)', 'date' => '2026-07-12', 'games' => ['Unreal Tournament 2004', 'Hearthstone - Battlegrounds', 'Mario Party']],
        ];

        foreach ($days as $day) {
            $schedule = Schedule::create(['name' => $day['name'], 'date' => $day['date']]);

            $startHour = 14;
            foreach ($day['games'] as $i => $gameName) {
                $g = $game($gameName);
                if (!$g) {
                    continue;
                }
                $schedule->games()->attach($g->id, [
                    'start_date' => Carbon::parse($day['date'])->setTime($startHour, 0),
                    'end_date' => Carbon::parse($day['date'])->setTime($startHour + 3, 0),
                    'is_tournament' => $i === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $startHour += 4;
            }
        }

        $this->command->info('Schedules created successfully!');
    }
}
