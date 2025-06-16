<?php

namespace Database\Seeders;

use App\Models\Tournament;
use Illuminate\Database\Seeder;
use App\Models\Game;
use App\Models\Schedule;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tournament::factory(10)->create([
            'schedule_id' => function () {
                return Schedule::inRandomOrder()->first()->id;
            },
            'game_id' => function () {
                return Game::inRandomOrder()->first()->id;
            },
        ]);
    }
}
