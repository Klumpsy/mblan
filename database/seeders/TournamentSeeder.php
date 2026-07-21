<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Database\Seeder;

class TournamentSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->all();
        if (empty($userIds)) {
            return;
        }

        $editions = Edition::with('schedules.games')->get();

        foreach ($editions as $edition) {
            $isCurrent = (int) $edition->year >= now()->year;
            $dayIndex = 0;

            foreach ($edition->schedules as $schedule) {
                $game = $schedule->games->first();
                if (!$game) {
                    continue;
                }

                $dayIndex++;
                // One active/live tournament on day 1 of the current edition; the rest concluded.
                $isActive = $isCurrent && $dayIndex === 1;
                $concluded = !$isActive;

                $tournament = Tournament::create([
                    'name' => $game->name . ' Cup',
                    'description' => 'The official ' . $game->name . ' bracket for ' . $edition->name . '.',
                    'is_active' => $isActive,
                    'concluded' => $concluded,
                    'time_start' => '14:00:00',
                    'time_end' => '17:00:00',
                    'game_id' => $game->id,
                    'schedule_id' => $schedule->id,
                    'is_team_based' => false,
                ]);

                // Attach a field of players with scores, then rank them.
                $players = collect($userIds)->shuffle()->take(min(8, count($userIds)));
                foreach ($players as $uid) {
                    $tournament->usersWithScores()->attach($uid, [
                        'score' => random_int(0, 100),
                    ]);
                }
                $tournament->updateRankings();
            }
        }
    }
}
