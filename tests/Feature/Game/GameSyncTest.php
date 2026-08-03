<?php

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The /game/sync endpoint persists the guest-cookie Arti Game stats as the
 * player's result within the active edition. Only a completed run counts, and
 * each completed run is authoritative: the latest attempt overwrites the
 * stored catches and time, so hitting "Opnieuw" and replaying truly resets
 * the recorded count (incomplete runs never pollute the leaderboard).
 */

function currentResult(User $user): ?GameResult
{
    return GameResult::where('user_id', $user->id)
        ->where('edition_id', Edition::current()->id)
        ->first();
}

function seedResult(User $user, array $attributes): GameResult
{
    return GameResult::create(array_merge(
        ['user_id' => $user->id, 'edition_id' => Edition::current()->id],
        $attributes,
    ));
}

test('guests cannot sync game stats', function () {
    $this->post(route('game.sync'), ['caught' => 3])
        ->assertRedirect(route('login'));
});

test('a completed run stores catches, completion and time', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 4, 'completed' => true, 'time' => 42000])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $result = currentResult($user);
    expect($result->catches)->toBe(4);
    expect($result->completed)->toBeTrue();
    expect($result->time_ms)->toBe(42000);
});

test('an incomplete run does not touch the record', function () {
    $user = User::factory()->create();
    seedResult($user, ['catches' => 5, 'completed' => true, 'time_ms' => 30000]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 99, 'completed' => false, 'time' => 1000])
        ->assertOk();

    $result = currentResult($user);
    expect($result->catches)->toBe(5);
    expect($result->time_ms)->toBe(30000);
});

test('a better completed run lowers the recorded catch count', function () {
    $user = User::factory()->create();
    seedResult($user, ['catches' => 8, 'completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true])
        ->assertOk();

    expect(currentResult($user)->catches)->toBe(3);
});

test('a later completed run overwrites the recorded catch count, even when worse', function () {
    $user = User::factory()->create();
    seedResult($user, ['catches' => 2, 'completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 9, 'completed' => true])
        ->assertOk();

    expect(currentResult($user)->catches)->toBe(9);
});

test('completion is sticky once achieved', function () {
    $user = User::factory()->create();
    seedResult($user, ['catches' => 4, 'completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 1, 'completed' => false])
        ->assertOk();

    expect(currentResult($user)->completed)->toBeTrue();
});

test('the latest completion time is stored', function () {
    $user = User::factory()->create();
    seedResult($user, ['catches' => 3, 'completed' => true, 'time_ms' => 30000]);

    // A slower run is the latest attempt, so it overwrites the record.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true, 'time' => 45000])
        ->assertOk();
    expect(currentResult($user)->time_ms)->toBe(45000);

    // A faster run does the same.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true, 'time' => 21000])
        ->assertOk();
    expect(currentResult($user)->time_ms)->toBe(21000);
});

test('negative catch counts are clamped to zero', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => -5, 'completed' => true])
        ->assertOk();

    expect(currentResult($user)->catches)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| De klassieker (space shooter): score-gebaseerd, hoogste wint
|--------------------------------------------------------------------------
*/

test('a completed space run stores the score for the active edition', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['score' => 1250, 'completed' => true])
        ->assertOk()
        ->assertJson(['ok' => true]);

    $result = currentResult($user);
    expect($result->score)->toBe(1250);
    expect($result->completed)->toBeTrue();
});

test('only a higher score overwrites the personal best', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('game.sync'), ['score' => 900, 'completed' => true]);
    $this->actingAs($user)->postJson(route('game.sync'), ['score' => 400, 'completed' => true]);
    expect(currentResult($user)->score)->toBe(900);

    $this->actingAs($user)->postJson(route('game.sync'), ['score' => 1500, 'completed' => true]);
    expect(currentResult($user)->score)->toBe(1500);
});

test('score runs stay apart per edition', function () {
    $user = User::factory()->create();
    $old = Edition::factory()->create();
    GameResult::create(['user_id' => $user->id, 'edition_id' => $old->id, 'completed' => true, 'score' => 5000]);

    $this->actingAs($user)->postJson(route('game.sync'), ['score' => 100, 'completed' => true]);

    expect(currentResult($user)->score)->toBe(100)
        ->and(GameResult::where('user_id', $user->id)->count())->toBe(2);
});
