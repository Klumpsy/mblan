<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The /game/sync endpoint persists the guest-cookie Arti Game stats onto the
 * signed-in account. Only a completed run counts, and it keeps the player's
 * personal best: fewest catches and fastest time, so replaying can only improve
 * their standing (and incomplete runs never pollute the leaderboard).
 */

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

    $user->refresh();
    expect($user->barn_catches)->toBe(4);
    expect((bool) $user->barn_completed)->toBeTrue();
    expect($user->barn_time_ms)->toBe(42000);
});

test('an incomplete run does not touch the record', function () {
    $user = User::factory()->create(['barn_catches' => 5, 'barn_completed' => true, 'barn_time_ms' => 30000]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 99, 'completed' => false, 'time' => 1000])
        ->assertOk();

    $user->refresh();
    expect($user->barn_catches)->toBe(5);
    expect($user->barn_time_ms)->toBe(30000);
});

test('a better completed run lowers the recorded catch count', function () {
    $user = User::factory()->create(['barn_catches' => 8, 'barn_completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true])
        ->assertOk();

    expect($user->refresh()->barn_catches)->toBe(3);
});

test('a worse completed run does not raise the recorded catch count', function () {
    $user = User::factory()->create(['barn_catches' => 2, 'barn_completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 9, 'completed' => true])
        ->assertOk();

    expect($user->refresh()->barn_catches)->toBe(2);
});

test('completion is sticky once achieved', function () {
    $user = User::factory()->create(['barn_completed' => true, 'barn_catches' => 4]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 1, 'completed' => false])
        ->assertOk();

    expect((bool) $user->refresh()->barn_completed)->toBeTrue();
});

test('only the fastest completion time is kept', function () {
    $user = User::factory()->create(['barn_time_ms' => 30000, 'barn_completed' => true, 'barn_catches' => 3]);

    // A slower run must not overwrite the record.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true, 'time' => 45000])
        ->assertOk();
    expect($user->refresh()->barn_time_ms)->toBe(30000);

    // A faster run does.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3, 'completed' => true, 'time' => 21000])
        ->assertOk();
    expect($user->refresh()->barn_time_ms)->toBe(21000);
});

test('negative catch counts are clamped to zero', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => -5, 'completed' => true])
        ->assertOk();

    expect($user->refresh()->barn_catches)->toBe(0);
});
