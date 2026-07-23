<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * The /game/sync endpoint persists the guest-cookie Arti Game stats onto the
 * signed-in account. It must be defensive: catches only ever climb (best run
 * kept via the cookie), completion is sticky, and the fastest time wins.
 */

test('guests cannot sync game stats', function () {
    $this->post(route('game.sync'), ['caught' => 3])
        ->assertRedirect(route('login'));
});

test('sync stores catches, completion and time', function () {
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

test('catch count never decreases (keeps the maximum seen)', function () {
    $user = User::factory()->create(['barn_catches' => 10]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 3])
        ->assertOk();

    expect($user->refresh()->barn_catches)->toBe(10);
});

test('completion is sticky once achieved', function () {
    $user = User::factory()->create(['barn_completed' => true]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 1, 'completed' => false])
        ->assertOk();

    expect((bool) $user->refresh()->barn_completed)->toBeTrue();
});

test('only the fastest completion time is kept', function () {
    $user = User::factory()->create(['barn_time_ms' => 30000, 'barn_completed' => true]);

    // A slower run must not overwrite the record.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['completed' => true, 'time' => 45000])
        ->assertOk();
    expect($user->refresh()->barn_time_ms)->toBe(30000);

    // A faster run does.
    $this->actingAs($user)
        ->postJson(route('game.sync'), ['completed' => true, 'time' => 21000])
        ->assertOk();
    expect($user->refresh()->barn_time_ms)->toBe(21000);
});

test('time is ignored when the run was not completed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => 2, 'completed' => false, 'time' => 15000])
        ->assertOk();

    expect($user->refresh()->barn_time_ms)->toBeNull();
});

test('negative catch counts are clamped to zero', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['caught' => -5])
        ->assertOk();

    expect($user->refresh()->barn_catches)->toBe(0);
});
