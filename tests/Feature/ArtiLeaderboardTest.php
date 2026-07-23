<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The Arti Game leaderboard (tournaments page) ranks players who reached the
 * barn: fewest catches first, ties broken by the fastest completion time.
 */

function artiRanking(): array
{
    $viewer = User::factory()->create();

    $names = [];
    app()->call(function () use (&$names) {
        $controller = new \App\Http\Controllers\TournamentController();
        $view = $controller->index();
        $names = $view->getData()['artiLeaderboard']->pluck('name')->all();
    });

    return $names;
}

test('only players who completed the barn appear', function () {
    $done = User::factory()->create(['name' => 'Finisher', 'barn_completed' => true, 'barn_catches' => 2]);
    User::factory()->create(['name' => 'Quitter', 'barn_completed' => false, 'barn_catches' => 0]);

    $this->actingAs($done);
    expect(artiRanking())->toBe(['Finisher']);
});

test('fewer catches ranks higher', function () {
    $a = User::factory()->create(['name' => 'Clean', 'barn_completed' => true, 'barn_catches' => 1]);
    User::factory()->create(['name' => 'Messy', 'barn_completed' => true, 'barn_catches' => 9]);

    $this->actingAs($a);
    expect(artiRanking())->toBe(['Clean', 'Messy']);
});

test('equal catches are broken by the fastest time', function () {
    $fast = User::factory()->create(['name' => 'Fast', 'barn_completed' => true, 'barn_catches' => 3, 'barn_time_ms' => 20000]);
    User::factory()->create(['name' => 'Slow', 'barn_completed' => true, 'barn_catches' => 3, 'barn_time_ms' => 55000]);

    $this->actingAs($fast);
    expect(artiRanking())->toBe(['Fast', 'Slow']);
});

test('players with a recorded time rank ahead of players without one', function () {
    $timed = User::factory()->create(['name' => 'Timed', 'barn_completed' => true, 'barn_catches' => 3, 'barn_time_ms' => 40000]);
    User::factory()->create(['name' => 'Untimed', 'barn_completed' => true, 'barn_catches' => 3, 'barn_time_ms' => null]);

    $this->actingAs($timed);
    expect(artiRanking())->toBe(['Timed', 'Untimed']);
});

test('the leaderboard is capped at twenty players', function () {
    User::factory()->count(25)->create(['barn_completed' => true, 'barn_catches' => 1]);

    $this->actingAs(User::factory()->create());
    expect(artiRanking())->toHaveCount(20);
});
