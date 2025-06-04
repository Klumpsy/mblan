<?php

namespace Tests\Feature\Livewire\Edition;

use App\Livewire\Edition\Schedule;
use App\Models\Edition;
use App\Models\Game;
use App\Models\Schedule as ScheduleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_correct_initial_schedule()
    {
        $edition = Edition::factory()->create([
            'name' => 'Test Edition',
            'year' => date('Y'),
        ]);

        $schedule1 = ScheduleModel::factory()
            ->forEdition($edition)
            ->create(['name' => 'Day 1']);

        $schedule2 = ScheduleModel::factory()
            ->forEdition($edition)
            ->create(['name' => 'Day 2']);

        $game1 = Game::factory()->create(['name' => 'Game One']);
        $game2 = Game::factory()->create(['name' => 'Game Two']);

        $today = now();
        $tomorrow = now()->addDay();

        $schedule1->games()->attach($game1, [
            'start_date' => $today->setTime(10, 0),
            'end_date' => $today->copy()->addHours(2),
        ]);

        $schedule2->games()->attach($game2, [
            'start_date' => $tomorrow->setTime(14, 0),
            'end_date' => $tomorrow->copy()->addHours(2),
        ]);

        Livewire::test(Schedule::class, ['edition' => $edition])
            ->assertSee('Day 1')
            ->assertSee('Game One')
            ->assertDontSee('Day 2')
            ->assertDontSee('Game Two');
    }

    public function test_it_changes_active_date_when_clicked()
    {
        $edition = Edition::factory()->create([
            'name' => 'Test Edition',
            'year' => date('Y'),
        ]);

        $schedule1 = ScheduleModel::factory()
            ->forEdition($edition)
            ->create(['name' => 'Day 1']);

        $schedule2 = ScheduleModel::factory()
            ->forEdition($edition)
            ->create(['name' => 'Day 2']);

        $game1 = Game::factory()->create(['name' => 'Game One']);
        $game2 = Game::factory()->create(['name' => 'Game Two']);

        $today = now();
        $tomorrow = now()->addDay();
        $tomorrowStr = $tomorrow->format('Y-m-d');

        $schedule1->games()->attach($game1, [
            'start_date' => $today->setTime(10, 0),
            'end_date' => $today->copy()->addHours(2),
        ]);

        $schedule2->games()->attach($game2, [
            'start_date' => $tomorrow->setTime(14, 0),
            'end_date' => $tomorrow->copy()->addHours(2),
        ]);

        Livewire::test(Schedule::class, ['edition' => $edition])
            ->assertSee('Day 1')
            ->assertSee('Game One')
            ->assertDontSee('Day 2')
            ->assertDontSee('Game Two')
            ->call('setActiveDate', $tomorrowStr)
            ->assertDontSee('Day 1')
            ->assertDontSee('Game One')
            ->assertSee('Day 2')
            ->assertSee('Game Two');
    }
}
