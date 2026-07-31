<?php

use App\Filament\Resources\TournamentResource\Pages\EditTournament;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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

test('registered players land on the scoreboard automatically when the tab opens', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $registered = User::factory()->create(['name' => 'Aangemeld Zonder Score']);
    $alreadyScored = User::factory()->create(['name' => 'Al Op Scorebord']);
    $notRegistered = User::factory()->create(['name' => 'Niet Aangemeld']);

    $tournament->registrations()->attach([$registered->id, $alreadyScored->id]);
    $tournament->usersWithScores()->attach($alreadyScored->id, ['score' => 5, 'ranking' => 1]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ]);

    $scoreboard = $tournament->usersWithScores()->get()->keyBy('id');

    expect($scoreboard->keys()->all())
        ->toContain($registered->id)          // pulled in automatically
        ->toContain($alreadyScored->id)
        ->not->toContain($notRegistered->id); // never signed up

    expect((int) $scoreboard[$registered->id]->pivot->score)->toBe(0);
    expect((int) $scoreboard[$alreadyScored->id]->pivot->score)->toBe(5); // untouched
});

test('time-based scores are added as minutes, seconds and milliseconds', function () {
    $tournament = Tournament::factory()->create([
        'scoring_type' => 'time',
        'higher_is_better' => false,
        'score_label' => 'Tijd',
        'is_team_based' => false,
    ]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 0, 'ranking' => 1]);

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ])
        ->callTableAction('addScore', $player, data: ['minutes' => 1, 'seconds' => 30, 'milliseconds' => 500])
        ->assertHasNoTableActionErrors();

    // 1:30.500 -> 90500 ms, added to a starting 0.
    expect((int) $tournament->usersWithScores()->where('users.id', $player->id)->first()->pivot->score)->toBe(90500);
});

test('creating teams posts the line-up to Discord when the toggle is on', function () {
    config(['discord.webhook_url' => 'https://discord.test/webhook']);
    Http::fake(['discord.test/*' => Http::response('', 204)]);

    $tournament = Tournament::factory()->create(['is_team_based' => true, 'name' => 'Team Cup']);
    User::factory()->count(4)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ])
        ->callTableAction('create_teams', data: ['team_size' => 2, 'post_to_discord' => true])
        ->assertHasNoTableActionErrors();

    Http::assertSent(fn ($r) => str_contains($r['embeds'][0]['title'] ?? '', 'Teams'));
});

test('creating teams does not post to Discord when the toggle is off', function () {
    config(['discord.webhook_url' => 'https://discord.test/webhook']);
    Http::fake(['discord.test/*' => Http::response('', 204)]);

    $tournament = Tournament::factory()->create(['is_team_based' => true]);
    User::factory()->count(4)->create()->each(
        fn ($u) => $tournament->usersWithScores()->attach($u->id, ['score' => 0])
    );

    Livewire::test(UsersRelationManager::class, [
        'ownerRecord' => $tournament,
        'pageClass' => EditTournament::class,
    ])
        ->callTableAction('create_teams', data: ['team_size' => 2, 'post_to_discord' => false])
        ->assertHasNoTableActionErrors();

    // A leader-change webhook may fire from ranking recalculation; what must NOT
    // happen is the team line-up being posted.
    Http::assertNotSent(fn ($r) => str_contains($r['embeds'][0]['title'] ?? '', 'Teams'));
});
