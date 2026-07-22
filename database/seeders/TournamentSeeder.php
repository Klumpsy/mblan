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

                // Rotate through scoring presets so the ladders show off the range.
                $presets = Tournament::scoringPresets();
                $keys = array_keys($presets);
                $presetKey = $keys[$dayIndex % count($keys)];
                $preset = $presets[$presetKey];

                $tournament = Tournament::create([
                    'name' => $game->name . ' Cup',
                    'description' => 'Het officiele ' . $game->name . ' toernooi voor ' . $edition->name . '.',
                    'is_active' => $isActive,
                    'concluded' => $concluded,
                    'time_start' => '14:00:00',
                    'time_end' => '17:00:00',
                    'game_id' => $game->id,
                    'schedule_id' => $schedule->id,
                    'is_team_based' => false,
                    'scoring_type' => $presetKey,
                    'score_label' => $preset['score_label'],
                    'higher_is_better' => $preset['higher_is_better'],
                ]);

                // Attach a full field (up to 20) of players with scores, then rank them.
                $fieldSize = $isActive ? min(20, count($userIds)) : random_int(8, min(16, count($userIds)));
                $players = collect($userIds)->shuffle()->take($fieldSize);
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
