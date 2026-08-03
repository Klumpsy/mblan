<?php

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The Arti Game leaderboard (tournaments page) ranks players who reached the
 * barn in the active edition: fewest catches first, ties broken by the
 * fastest completion time.
 */

function artiPlayer(string $name, array $result): User
{
    $user = User::factory()->create(['name' => $name]);

    GameResult::create(array_merge(
        ['user_id' => $user->id, 'edition_id' => Edition::current()->id],
        $result,
    ));

    return $user;
}

function artiRanking(): array
{
    $names = [];
    app()->call(function () use (&$names) {
        $controller = new \App\Http\Controllers\TournamentController();
        $view = $controller->index();
        $names = $view->getData()['artiLeaderboard']->pluck('name')->all();
    });

    return $names;
}

test('only players who completed the barn appear', function () {
    $done = artiPlayer('Finisher', ['completed' => true, 'catches' => 2]);
    artiPlayer('Quitter', ['completed' => false, 'catches' => 0]);

    $this->actingAs($done);
    expect(artiRanking())->toBe(['Finisher']);
});

test('fewer catches ranks higher', function () {
    $a = artiPlayer('Clean', ['completed' => true, 'catches' => 1]);
    artiPlayer('Messy', ['completed' => true, 'catches' => 9]);

    $this->actingAs($a);
    expect(artiRanking())->toBe(['Clean', 'Messy']);
});

test('equal catches are broken by the fastest time', function () {
    $fast = artiPlayer('Fast', ['completed' => true, 'catches' => 3, 'time_ms' => 20000]);
    artiPlayer('Slow', ['completed' => true, 'catches' => 3, 'time_ms' => 55000]);

    $this->actingAs($fast);
    expect(artiRanking())->toBe(['Fast', 'Slow']);
});

test('players with a recorded time rank ahead of players without one', function () {
    $timed = artiPlayer('Timed', ['completed' => true, 'catches' => 3, 'time_ms' => 40000]);
    artiPlayer('Untimed', ['completed' => true, 'catches' => 3, 'time_ms' => null]);

    $this->actingAs($timed);
    expect(artiRanking())->toBe(['Timed', 'Untimed']);
});

test('results from another edition do not appear', function () {
    $old = Edition::factory()->create();
    $user = User::factory()->create(['name' => 'Nostalgicus']);
    GameResult::create(['user_id' => $user->id, 'edition_id' => $old->id, 'completed' => true, 'catches' => 0]);

    $this->actingAs($user);
    expect(artiRanking())->toBe([]);
});

test('the leaderboard is capped at twenty players', function () {
    User::factory()->count(25)->create()->each(function (User $user) {
        GameResult::create([
            'user_id' => $user->id,
            'edition_id' => Edition::current()->id,
            'completed' => true,
            'catches' => 1,
        ]);
    });

    $this->actingAs(User::factory()->create());
    expect(artiRanking())->toHaveCount(20);
});
