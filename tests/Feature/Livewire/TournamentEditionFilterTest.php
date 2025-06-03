<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TournamentEditionFilter;
use App\Models\Edition;
use App\Models\Schedule;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TournamentEditionFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 6, 3, 12, 0, 0));
    }

    public function testRendersWithYearDropdownAndSections()
    {
        $user = User::factory()->create();
        $edition = Edition::factory()->create(['year' => 2025]);
        $schedule = Schedule::factory()->create(['date' => '2025-06-03', 'edition_id' => $edition->id]);
        $tournament = Tournament::factory()->create([
            'name' => 'Test Tournament',
            'is_active' => true,
            'time_start' => '14:00:00',
            'schedule_id' => $schedule->id,
        ]);

        Livewire::actingAs($user)
            ->test(TournamentEditionFilter::class)
            ->assertStatus(200)
            ->assertSee('2025')
            ->assertSee('Active tournament')
            ->assertSee('Upcoming tournaments')
            ->assertSee('Past tournaments')
            ->assertSee('Test Tournament');
    }
}
