<?php

namespace Database\Seeders;

use App\Models\Schedule;
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

        $presets = Tournament::scoringPresets();
        $keys = array_keys($presets);
        $dayIndex = 0;

        foreach (Schedule::with('games')->orderBy('date')->get() as $schedule) {
            $game = $schedule->games->first();
            if (!$game) {
                continue;
            }

            $dayIndex++;
            $isActive = $dayIndex === 1;             // one live tournament
            $presetKey = $keys[$dayIndex % count($keys)];
            $preset = $presets[$presetKey];

            $tournament = Tournament::create([
                'name' => $game->name . ' Cup',
                'description' => 'Het officiele ' . $game->name . ' toernooi.',
                'is_active' => $isActive,
                'concluded' => !$isActive,
                'time_start' => '14:00:00',
                'time_end' => '17:00:00',
                'game_id' => $game->id,
                'schedule_id' => $schedule->id,
                'is_team_based' => false,
                'scoring_type' => $presetKey,
                'score_label' => $preset['score_label'],
                'higher_is_better' => $preset['higher_is_better'],
            ]);

            $fieldSize = $isActive ? min(20, count($userIds)) : random_int(8, min(16, count($userIds)));
            foreach (collect($userIds)->shuffle()->take($fieldSize) as $uid) {
                $tournament->usersWithScores()->attach($uid, ['score' => random_int(0, 100)]);
            }
            $tournament->updateRankings();
        }
    }
}
