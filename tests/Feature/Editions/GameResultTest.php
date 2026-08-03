<?php

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\User;

it('stores a completed run as a game result for the active edition', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('game.sync'), [
        'caught' => 3, 'completed' => true, 'time' => 61500,
    ])->assertOk();

    $result = GameResult::where('user_id', $user->id)->sole();
    expect($result->edition_id)->toBe(Edition::current()->id)
        ->and($result->catches)->toBe(3)
        ->and($result->completed)->toBeTrue()
        ->and($result->time_ms)->toBe(61500);
});

it('overwrites the previous run instead of adding rows', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('game.sync'), ['caught' => 5, 'completed' => true, 'time' => 90000]);
    $this->actingAs($user)->postJson(route('game.sync'), ['caught' => 2, 'completed' => true, 'time' => 45000]);

    expect(GameResult::where('user_id', $user->id)->count())->toBe(1)
        ->and(GameResult::where('user_id', $user->id)->sole()->catches)->toBe(2);
});

it('keeps results apart per edition', function () {
    $user = User::factory()->create();
    $old = Edition::factory()->create();
    GameResult::create(['user_id' => $user->id, 'edition_id' => $old->id, 'catches' => 9, 'completed' => true, 'time_ms' => 120000]);

    $this->actingAs($user)->postJson(route('game.sync'), ['caught' => 1, 'completed' => true, 'time' => 30000]);

    expect(GameResult::where('user_id', $user->id)->count())->toBe(2);
});
