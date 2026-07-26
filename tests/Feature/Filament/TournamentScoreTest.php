<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('an admin can edit a player score and it persists to the pivot', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 10, 'ranking' => 1]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ])
        ->callTableAction('edit', $player, data: ['score' => 42])
        ->assertHasNoTableActionErrors();

    expect((int) $tournament->usersWithScores()->where('users.id', $player->id)->first()->pivot->score)->toBe(42);
});

test('an admin can quickly add seconds/points to a running score', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => false, 'score_label' => 'Seconden']);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 10, 'ranking' => 1]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ])
        ->callTableAction('addScore', $player, data: ['amount' => 30])
        ->assertHasNoTableActionErrors();

    expect((int) $tournament->usersWithScores()->where('users.id', $player->id)->first()->pivot->score)->toBe(40);
});

test('only signed-up players who are not yet scored can be given a score', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $registeredUnscored = User::factory()->create(['name' => 'Aangemeld Zonder Score']);
    $registeredScored = User::factory()->create(['name' => 'Al Op Scorebord']);
    $notRegistered = User::factory()->create(['name' => 'Niet Aangemeld']);

    $tournament->registrations()->attach([$registeredUnscored->id, $registeredScored->id]);
    $tournament->usersWithScores()->attach($registeredScored->id, ['score' => 5, 'ranking' => 1]);

    $component = Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ]);

    $eligibleIds = $component->instance()->eligiblePlayers()->keys()->all();

    expect($eligibleIds)
        ->toContain($registeredUnscored->id)   // signed up, no score yet
        ->not->toContain($registeredScored->id) // already on the scoreboard
        ->not->toContain($notRegistered->id);   // never signed up
});
