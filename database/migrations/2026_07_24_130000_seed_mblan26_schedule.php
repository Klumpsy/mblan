<?php

use App\Models\Game;
use App\Models\Schedule;
use App\Models\ScheduleBlock;
use Illuminate\Database\Migrations\Migration;

/**
 * One-time content setup for the MBLAN 2026 speelschema.
 *
 * Fills the three schedule days (Vrijdag, Zaterdag, Zondag) with their
 * games (start/end times, tournament flag) and the non-game blocks
 * (Vrije tijd, eten, BBQ, prijsuitreiking, opruimen). Idempotent: game
 * pivots are synced and blocks are replaced, so re-running is safe.
 */
return new class extends Migration
{
    private array $days = [
        'Vrijdag' => [
            'date' => '2026-07-31',
            'games' => [
                ['Warcraft III: Reforged', '2026-07-31 14:00:00', '2026-07-31 16:30:00', false],
                ['Team Fortress 2',        '2026-07-31 18:00:00', '2026-07-31 20:00:00', false],
                ['Hearthstone',            '2026-07-31 20:00:00', '2026-07-31 22:15:00', true],
                ['Fall Guys',              '2026-07-31 22:15:00', '2026-08-01 00:00:00', false],
                ['MECCHA CHAMELEON',       '2026-08-01 00:00:00', '2026-08-01 02:00:00', false],
                ['Among Us',               '2026-08-01 00:00:00', '2026-08-01 02:00:00', false],
            ],
            'blocks' => [
                ['Binnenkomst & Setup',    '2026-07-31 13:00:00', '2026-07-31 14:00:00'],
                ['Pizza bestellen en eten','2026-07-31 16:30:00', '2026-07-31 18:00:00'],
            ],
        ],
        'Zaterdag' => [
            'date' => '2026-08-01',
            'games' => [
                ['PEAK',              '2026-08-01 12:30:00', '2026-08-01 14:00:00', false],
                ['Brawlhalla',        '2026-08-01 14:00:00', '2026-08-01 15:00:00', false],
                ['Call of Duty 2',    '2026-08-01 15:00:00', '2026-08-01 17:30:00', false],
                ['Zeepkist',          '2026-08-01 20:00:00', '2026-08-01 22:00:00', true],
                ['Pummel Party',      '2026-08-01 22:00:00', '2026-08-02 00:00:00', false],
                ['Rainbow Six Siege', '2026-08-01 22:00:00', '2026-08-02 00:00:00', false],
            ],
            'blocks' => [
                ['Vrij gamen',                    '2026-08-01 09:00:00', '2026-08-01 12:30:00'],
                ['BBQ',                           '2026-08-01 17:30:00', '2026-08-01 20:00:00'],
                ['Vrije games voor de diehards',  '2026-08-02 00:00:00', '2026-08-02 03:00:00'],
            ],
        ],
        'Zondag' => [
            'date' => '2026-08-02',
            'games' => [
                ['Teamfight Tactics', '2026-08-02 10:00:00', '2026-08-02 11:00:00', false],
            ],
            'blocks' => [
                ['Vrij gamen',              '2026-08-02 09:00:00', '2026-08-02 10:00:00'],
                ['Prijsuitreiking',         '2026-08-02 11:00:00', '2026-08-02 11:30:00'],
                ['Vrij gamen',              '2026-08-02 11:30:00', '2026-08-02 13:00:00'],
                ['Opruimen en naar huis',   '2026-08-02 13:00:00', null],
            ],
        ],
    ];

    public function up(): void
    {
        $gameIds = Game::pluck('id', 'name');

        foreach ($this->days as $name => $day) {
            $schedule = Schedule::firstOrCreate(['name' => $name], ['date' => $day['date']]);
            $schedule->update(['date' => $day['date']]);

            $sync = [];
            foreach ($day['games'] as [$gameName, $start, $end, $isTournament]) {
                if (! isset($gameIds[$gameName])) {
                    continue;
                }
                $sync[$gameIds[$gameName]] = [
                    'start_date' => $start,
                    'end_date' => $end,
                    'is_tournament' => $isTournament,
                ];
            }
            $schedule->games()->sync($sync);

            $schedule->blocks()->delete();
            foreach ($day['blocks'] as [$title, $start, $end]) {
                ScheduleBlock::create([
                    'schedule_id' => $schedule->id,
                    'title' => $title,
                    'start_date' => $start,
                    'end_date' => $end,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->days) as $name) {
            $schedule = Schedule::where('name', $name)->first();
            if ($schedule) {
                $schedule->games()->detach();
                $schedule->blocks()->delete();
            }
        }
    }
};
