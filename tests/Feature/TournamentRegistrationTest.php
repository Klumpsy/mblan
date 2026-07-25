<?php

use App\Livewire\Tournament\Ladder;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a player can sign up for a tournament and withdraw again', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $tournament = Tournament::factory()->create(['concluded' => false]);

    $this->actingAs($user);

    Livewire::test(Ladder::class, ['tournament' => $tournament])
        ->assertSet('tournament.id', $tournament->id)
        ->call('toggleRegister');

    expect($tournament->registrations()->whereKey($user->id)->exists())->toBeTrue();

    Livewire::test(Ladder::class, ['tournament' => $tournament])
        ->call('toggleRegister');

    expect($tournament->fresh()->registrations()->whereKey($user->id)->exists())->toBeFalse();
});

test('sign-up is blocked once a tournament is concluded', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $tournament = Tournament::factory()->create(['concluded' => true]);

    $this->actingAs($user);

    Livewire::test(Ladder::class, ['tournament' => $tournament])
        ->call('toggleRegister');

    expect($tournament->registrations()->count())->toBe(0);
});
