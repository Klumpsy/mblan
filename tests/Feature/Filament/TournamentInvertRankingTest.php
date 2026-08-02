<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('the invert action flips higher_is_better and reverses the ranking', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $high = User::factory()->create(['name' => 'Hoog']);
    $low = User::factory()->create(['name' => 'Laag']);
    $tournament->usersWithScores()->attach($high->id, ['score' => 100]);
    $tournament->usersWithScores()->attach($low->id, ['score' => 10]);
    $tournament->recalculateRankings();

    $rank = fn ($user) => (int) $tournament->usersWithScores()->where('users.id', $user->id)->first()->pivot->ranking;
    expect($rank($high))->toBe(1);

    Livewire::test(EditTournament::class, ['record' => $tournament->id])
        ->callAction('invertRanking');

    expect($tournament->fresh()->higher_is_better)->toBeFalse()
        ->and($rank($low))->toBe(1)
        ->and($rank($high))->toBe(2);
});

test('inverting twice restores the original order', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $high = User::factory()->create();
    $low = User::factory()->create();
    $tournament->usersWithScores()->attach($high->id, ['score' => 100]);
    $tournament->usersWithScores()->attach($low->id, ['score' => 10]);
    $tournament->recalculateRankings();

    Livewire::test(EditTournament::class, ['record' => $tournament->id])->callAction('invertRanking');
    Livewire::test(EditTournament::class, ['record' => $tournament->id])->callAction('invertRanking');

    $rank = fn ($user) => (int) $tournament->usersWithScores()->where('users.id', $user->id)->first()->pivot->ranking;
    expect($tournament->fresh()->higher_is_better)->toBeTrue()
        ->and($rank($high))->toBe(1)
        ->and($rank($low))->toBe(2);
});

test('inverting a team tournament reverses the team ranking', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true, 'higher_is_better' => true]);
    [$a, $b] = User::factory()->count(2)->create();
    $tournament->usersWithScores()->attach($a->id, ['score' => 0, 'team_number' => 1, 'team_score' => 50]);
    $tournament->usersWithScores()->attach($b->id, ['score' => 0, 'team_number' => 2, 'team_score' => 5]);
    $tournament->recalculateRankings();

    Livewire::test(EditTournament::class, ['record' => $tournament->id])->callAction('invertRanking');

    $rank = fn ($user) => (int) $tournament->usersWithScores()->where('users.id', $user->id)->first()->pivot->ranking;
    expect($rank($b))->toBe(1)->and($rank($a))->toBe(2);
});
