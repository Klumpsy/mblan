<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

test('individual tournaments can be split into lobbies too', function () {
    // Hearthstone-style: 14 solo players dealt into 2 lobbies.
    $tournament = Tournament::factory()->create(['is_team_based' => false]);
    User::factory()->count(14)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    teamMaker($tournament)
        ->callTableAction('create_teams', data: ['mode' => 'team_count', 'team_count' => 2])
        ->assertHasNoTableActionErrors();

    expect(teamSizes($tournament))->toBe([7, 7]);
});

test('lobby mates in an individual tournament keep their own score and ranking', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    [$a, $b] = User::factory()->count(2)->create();
    $tournament->usersWithScores()->attach([$a->id => ['score' => 0], $b->id => ['score' => 0]]);

    teamMaker($tournament)
        ->callTableBulkAction('create_team_from_selection', [$a->id, $b->id], data: ['team_name' => 'Lobby 1'])
        ->assertHasNoTableActionErrors();

    // Both players sit in the same lobby; scoring one must not touch the other.
    teamMaker($tournament)
        ->callTableAction('addScore', $a, data: ['amount' => 25])
        ->assertHasNoTableActionErrors();

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');

    expect((int) $pivots[$a->id]->pivot->score)->toBe(25);
    expect((int) $pivots[$b->id]->pivot->score)->toBe(0);
    expect((int) $pivots[$a->id]->pivot->ranking)->toBe(1);
    expect((int) $pivots[$b->id]->pivot->ranking)->toBe(2);
});

test('an admin can announce the current line-up to Discord with one click', function () {
    config(['discord.webhook_url' => 'https://discord.test/webhook']);
    Http::fake(['discord.test/*' => Http::response('', 204)]);

    $tournament = Tournament::factory()->create(['is_team_based' => false, 'name' => 'Hearthstone Cup']);
    User::factory()->count(2)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, [
            'score' => 0, 'team_name' => 'Lobby 1', 'team_number' => 1,
        ])
    );

    teamMaker($tournament)
        ->callTableAction('announce_teams')
        ->assertHasNoTableActionErrors();

    Http::assertSent(fn ($r) => str_contains($r['embeds'][0]['title'] ?? '', 'Teams')
        && str_contains($r['embeds'][0]['description'] ?? '', 'Lobby 1'));
});

test('the announce button stays hidden until there is a line-up', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 0]);

    teamMaker($tournament)->assertTableActionHidden('announce_teams');
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
