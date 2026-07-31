<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManager\RoundsRelationManager;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

function roundsManager(Tournament $tournament)
{
    return Livewire::test(RoundsRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ]);
}

test('an admin can save a round with points per player and the totals follow', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    [$a, $b] = User::factory()->count(2)->create();
    $tournament->usersWithScores()->attach([$a->id => ['score' => 0], $b->id => ['score' => 0]]);

    roundsManager($tournament)
        ->callTableAction('add_round', data: [
            'name' => 'Kwalificatie',
            'player_points' => [$a->id => 15, $b->id => 9],
        ])
        ->assertHasNoTableActionErrors();

    roundsManager($tournament)
        ->callTableAction('add_round', data: [
            'player_points' => [$a->id => 1, $b->id => 30],
        ])
        ->assertHasNoTableActionErrors();

    expect($tournament->rounds()->count())->toBe(2);
    expect($tournament->rounds()->first()->name)->toBe('Kwalificatie');

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');
    expect((int) $pivots[$a->id]->pivot->score)->toBe(16);
    expect((int) $pivots[$b->id]->pivot->score)->toBe(39);
    expect((int) $pivots[$b->id]->pivot->ranking)->toBe(1);
});

test('an admin can save a round with points per team', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true, 'higher_is_better' => true]);
    $players = User::factory()->count(4)->create();

    foreach ($players->slice(0, 2) as $p) {
        $tournament->usersWithScores()->attach($p->id, ['score' => 0, 'team_name' => 'Team 1', 'team_number' => 1]);
    }
    foreach ($players->slice(2, 2) as $p) {
        $tournament->usersWithScores()->attach($p->id, ['score' => 0, 'team_name' => 'Team 2', 'team_number' => 2]);
    }

    roundsManager($tournament)
        ->callTableAction('add_round', data: [
            'team_points' => [1 => 5, 2 => 12],
        ])
        ->assertHasNoTableActionErrors();

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');

    expect((int) $pivots[$players[0]->id]->pivot->team_score)->toBe(5);
    expect((int) $pivots[$players[2]->id]->pivot->team_score)->toBe(12);
    expect((int) $pivots[$players[2]->id]->pivot->ranking)->toBe(1);
    expect((int) $pivots[$players[0]->id]->pivot->ranking)->toBe(2);
});

test('editing the points of a round corrects the totals', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 0]);

    roundsManager($tournament)
        ->callTableAction('add_round', data: ['player_points' => [$player->id => 10]])
        ->assertHasNoTableActionErrors();

    $round = $tournament->rounds()->first();

    roundsManager($tournament)
        ->callTableAction('edit_points', $round, data: ['player_points' => [$player->id => 4]])
        ->assertHasNoTableActionErrors();

    expect((int) $tournament->usersWithScores()->first()->pivot->score)->toBe(4);
});

test('deleting a round subtracts its points from the totals', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 0]);

    roundsManager($tournament)
        ->callTableAction('add_round', data: ['player_points' => [$player->id => 10]])
        ->assertHasNoTableActionErrors();
    roundsManager($tournament)
        ->callTableAction('add_round', data: ['player_points' => [$player->id => 7]])
        ->assertHasNoTableActionErrors();

    $second = $tournament->rounds()->where('number', 2)->first();

    roundsManager($tournament)
        ->callTableAction('delete', $second)
        ->assertHasNoTableActionErrors();

    expect($tournament->rounds()->count())->toBe(1);
    expect((int) $tournament->usersWithScores()->first()->pivot->score)->toBe(10);
});
