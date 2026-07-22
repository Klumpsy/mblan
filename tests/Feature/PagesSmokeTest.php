<?php

use App\Models\Blog;
use App\Models\Edition;
use App\Models\Game;
use App\Models\Schedule;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Every primary page renders (HTTP 200) for a signed-in, verified user with a
 * realistic set of seeded content, including the live tournament ladder.
 */
beforeEach(function () {
    $this->user = User::factory()->create(['email_verified_at' => now()]);

    $this->edition = Edition::factory()->create([
        'is_active' => true,
        'is_exclusive' => false,
        'color' => '#65E59A',
    ]);

    $game = Game::factory()->create();
    $schedule = Schedule::factory()->create(['edition_id' => $this->edition->id]);
    $schedule->games()->attach($game->id, [
        'start_date' => now(),
        'end_date' => now()->addHours(2),
    ]);

    $tournament = Tournament::factory()->create([
        'is_active' => true,
        'schedule_id' => $schedule->id,
        'game_id' => $game->id,
        'score_label' => 'Punten',
        'higher_is_better' => true,
    ]);
    $tournament->usersWithScores()->attach($this->user->id, ['score' => 42]);
    $tournament->updateRankings();

    Blog::factory()->create(['published' => true, 'published_at' => now()]);
});

test('landing page renders with ladder', function () {
    $this->get('/')->assertOk()->assertSee('MBLAN');
});

test('dashboard redirects to schedule', function () {
    $this->actingAs($this->user)->get('/dashboard')->assertRedirect('/schedule');
});

test('schedule (game roster) renders', function () {
    $this->actingAs($this->user)->get(route('schedule'))->assertOk();
});

test('tournaments page renders the ladder', function () {
    $this->actingAs($this->user)->get(route('tournaments'))->assertOk();
});

test('profile page renders', function () {
    $this->actingAs($this->user)->get(route('profile.show'))->assertOk();
});

test('guests are redirected to login from protected pages', function () {
    $this->get(route('schedule'))->assertRedirect(route('login'));
    $this->get(route('tournaments'))->assertRedirect(route('login'));
});
