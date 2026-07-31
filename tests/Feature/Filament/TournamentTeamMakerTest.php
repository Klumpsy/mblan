<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

function teamMaker(Tournament $tournament)
{
    return Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ]);
}

function teamSizes(Tournament $tournament): array
{
    return DB::table('tournament_user')
        ->where('tournament_id', $tournament->id)
        ->whereNotNull('team_number')
        ->selectRaw('team_number, count(*) as size')
        ->groupBy('team_number')
        ->pluck('size', 'team_number')
        ->sort()
        ->values()
        ->all();
}

test('teams can be made by choosing the number of teams', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true]);
    User::factory()->count(8)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    teamMaker($tournament)
        ->callTableAction('create_teams', data: ['mode' => 'team_count', 'team_count' => 3])
        ->assertHasNoTableActionErrors();

    expect(teamSizes($tournament))->toBe([2, 3, 3]);
});

test('teams can be made by choosing the players per team', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true]);
    User::factory()->count(6)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    teamMaker($tournament)
        ->callTableAction('create_teams', data: ['mode' => 'team_size', 'team_size' => 3])
        ->assertHasNoTableActionErrors();

    expect(teamSizes($tournament))->toBe([3, 3]);
});

test('a leftover player joins a team instead of becoming a solo team', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true]);
    User::factory()->count(5)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    teamMaker($tournament)
        ->callTableAction('create_teams', data: ['mode' => 'team_size', 'team_size' => 2])
        ->assertHasNoTableActionErrors();

    expect(teamSizes($tournament))->toBe([2, 3]);

    $names = DB::table('tournament_user')
        ->where('tournament_id', $tournament->id)
        ->pluck('team_name');
    expect($names->filter(fn ($n) => str_starts_with((string) $n, 'Solo')))->toBeEmpty();
});

test('registered players who are not on the scoreboard yet are included in the draw', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true]);

    $scored = User::factory()->count(2)->create();
    $scored->each(fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0]));

    $registeredOnly = User::factory()->count(2)->create();
    $tournament->registrations()->attach($registeredOnly->pluck('id'));

    teamMaker($tournament)
        ->callTableAction('create_teams', data: ['mode' => 'team_count', 'team_count' => 2])
        ->assertHasNoTableActionErrors();

    $assigned = DB::table('tournament_user')
        ->where('tournament_id', $tournament->id)
        ->whereNotNull('team_number')
        ->pluck('user_id')
        ->sort()
        ->values()
        ->all();

    expect($assigned)->toBe(
        $scored->pluck('id')->merge($registeredOnly->pluck('id'))->sort()->values()->all()
    );
});

test('shuffling clears the old teams and deals everyone again', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true]);
    User::factory()->count(4)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, [
            'score' => 0, 'team_name' => 'Oud team', 'team_number' => 99,
        ])
    );

    teamMaker($tournament)
        ->callTableAction('shuffle_teams', data: ['mode' => 'team_count', 'team_count' => 2])
        ->assertHasNoTableActionErrors();

    expect(teamSizes($tournament))->toBe([2, 2]);
    expect(DB::table('tournament_user')->where('tournament_id', $tournament->id)->max('team_number'))->toBe(2);
});
