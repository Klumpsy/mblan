<?php

use App\Models\Game;
use App\Models\Schedule;
use App\Models\Tournament;
use Illuminate\Database\Migrations\Migration;

/**
 * Sets up the two MBLAN 2026 tournaments: the Best of 4 Hearthstone
 * toernooi (Vrijdag) and the Zeepkist toernooi (Zaterdag). Idempotent
 * via updateOrCreate keyed on the tournament name.
 */
return new class extends Migration
{
    public function up(): void
    {
        $games = Game::pluck('id', 'name');
        $schedules = Schedule::pluck('id', 'name');

        $tournaments = [
            [
                'name' => 'Best of 4 Hearthstone Toernooi',
                'game' => 'Hearthstone',
                'schedule' => 'Vrijdag',
                'time_start' => '20:00:00',
                'time_end' => '22:15:00',
                'description' => 'Best of 4 knockout toernooi. Speel solo en versla je tegenstanders in spannende potjes Hearthstone.',
                'scoring_type' => 'wins',
                'score_label' => 'Wins',
                'higher_is_better' => true,
                'is_team_based' => false,
            ],
            [
                'name' => 'Zeepkist Toernooi',
                'game' => 'Zeepkist',
                'schedule' => 'Zaterdag',
                'time_start' => '20:00:00',
                'time_end' => '22:00:00',
                'description' => 'Race solo tegen elkaar over gekke banen. De snelste tijd wint dit Zeepkist toernooi.',
                'scoring_type' => 'time',
                'score_label' => 'Seconden',
                'higher_is_better' => false,
                'is_team_based' => false,
            ],
        ];

        foreach ($tournaments as $t) {
            if (! isset($games[$t['game']], $schedules[$t['schedule']])) {
                continue;
            }

            Tournament::updateOrCreate(
                ['name' => $t['name']],
                [
                    'game_id' => $games[$t['game']],
                    'schedule_id' => $schedules[$t['schedule']],
                    'time_start' => $t['time_start'],
                    'time_end' => $t['time_end'],
                    'description' => $t['description'],
                    'scoring_type' => $t['scoring_type'],
                    'score_label' => $t['score_label'],
                    'higher_is_better' => $t['higher_is_better'],
                    'is_team_based' => $t['is_team_based'],
                    'is_active' => false,
                    'concluded' => false,
                ]
            );
        }
    }

    public function down(): void
    {
        Tournament::whereIn('name', [
            'Best of 4 Hearthstone Toernooi',
            'Zeepkist Toernooi',
        ])->delete();
    }
};
