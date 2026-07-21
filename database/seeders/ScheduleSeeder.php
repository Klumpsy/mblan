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
        $editions = Edition::whereIn('slug', ['mblan24', 'mblan25', 'mblan26'])
            ->get()
            ->keyBy('slug');

        if ($editions->count() < 3) {
            $this->command->error('Editions not found! Run EditionSeeder first.');
            return;
        }

        $games = Game::all();
        if ($games->isEmpty()) {
            $this->command->error('No games found! Run GameSeeder first.');
            return;
        }

        $game = fn ($name) => $games->firstWhere('name', $name);

        $plan = [
            'mblan24' => [
                ['name' => 'Day 1 - Opening', 'date' => '2024-06-10', 'games' => ['Warcraft III', 'Hearthstone - Battlegrounds']],
                ['name' => 'Day 2 - Main Event', 'date' => '2024-06-11', 'games' => ['Team Fortress 2', 'Age of Empires III', 'Among Us']],
                ['name' => 'Day 3 - Finals', 'date' => '2024-06-12', 'games' => ['Diablo III', 'Unreal Tournament 2004']],
            ],
            'mblan25' => [
                ['name' => 'Day 1 - Opening Ceremony', 'date' => '2025-07-15', 'games' => ['Battlefield Vietnam', 'Trackmania']],
                ['name' => 'Day 2 - Main Tournament', 'date' => '2025-07-16', 'games' => ['Warcraft III', 'Team Fortress 2', 'Mario Party']],
                ['name' => 'Day 3 - Finals Day', 'date' => '2025-07-17', 'games' => ['Age of Empires III', 'Among Us', 'Hearthstone - Battlegrounds']],
            ],
            'mblan26' => [
                ['name' => 'Day 1 - Ignition', 'date' => '2026-07-10', 'games' => ['Trackmania', 'Team Fortress 2']],
                ['name' => 'Day 2 - The Anvil', 'date' => '2026-07-11', 'games' => ['Warcraft III', 'Age of Empires III', 'Among Us']],
                ['name' => 'Day 3 - Tempered Steel (Finals)', 'date' => '2026-07-12', 'games' => ['Unreal Tournament 2004', 'Hearthstone - Battlegrounds', 'Mario Party']],
            ],
        ];

        foreach ($plan as $slug => $days) {
            $edition = $editions->get($slug);

            foreach ($days as $day) {
                $schedule = Schedule::create([
                    'name' => $day['name'],
                    'edition_id' => $edition->id,
                    'date' => $day['date'],
                ]);

                $startHour = 10;
                foreach ($day['games'] as $gameName) {
                    $g = $game($gameName);
                    if (!$g) {
                        continue;
                    }

                    $endHour = $startHour + 3;
                    $schedule->games()->attach($g->id, [
                        'start_date' => Carbon::parse($day['date'])->setTime($startHour, 0),
                        'end_date' => Carbon::parse($day['date'])->setTime($endHour, 0),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $startHour = $endHour + 1;
                }
            }
        }

        $this->command->info('Schedules created successfully!');
    }
}
