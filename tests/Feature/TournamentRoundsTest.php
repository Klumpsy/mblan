<?php

use App\Models\Tournament;
use App\Models\TournamentRound;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function roundWithPoints(Tournament $tournament, array $pointsByUserId, ?string $name = null): TournamentRound
{
    $round = $tournament->rounds()->create([
        'number' => $tournament->nextRoundNumber(),
        'name' => $name,
    ]);

    foreach ($pointsByUserId as $userId => $points) {
        $round->scores()->create(['user_id' => $userId, 'points' => $points]);
    }

    $tournament->applyRoundTotals();

    return $round;
}

test('round points add up to the player totals and rankings', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    [$a, $b] = User::factory()->count(2)->create();
    $tournament->usersWithScores()->attach([$a->id => ['score' => 0], $b->id => ['score' => 0]]);

    roundWithPoints($tournament, [$a->id => 10, $b->id => 5]);
    roundWithPoints($tournament, [$a->id => 2, $b->id => 20]);

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');

    expect((int) $pivots[$a->id]->pivot->score)->toBe(12);
    expect((int) $pivots[$b->id]->pivot->score)->toBe(25);
    expect((int) $pivots[$b->id]->pivot->ranking)->toBe(1);
    expect((int) $pivots[$a->id]->pivot->ranking)->toBe(2);
});

test('lower-is-better tournaments rank the lowest round total first', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => false]);
    [$fast, $slow] = User::factory()->count(2)->create();
    $tournament->usersWithScores()->attach([$fast->id => ['score' => 0], $slow->id => ['score' => 0]]);

    roundWithPoints($tournament, [$fast->id => 30, $slow->id => 90]);

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');

    expect((int) $pivots[$fast->id]->pivot->ranking)->toBe(1);
    expect((int) $pivots[$slow->id]->pivot->ranking)->toBe(2);
});

test('team tournaments carry round totals into the team score and team ranking', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => true, 'higher_is_better' => true]);
    $players = User::factory()->count(4)->create();

    // Two teams of two.
    foreach ($players->slice(0, 2) as $p) {
        $tournament->usersWithScores()->attach($p->id, ['score' => 0, 'team_name' => 'Team 1', 'team_number' => 1]);
    }
    foreach ($players->slice(2, 2) as $p) {
        $tournament->usersWithScores()->attach($p->id, ['score' => 0, 'team_name' => 'Team 2', 'team_number' => 2]);
    }

    // Round points are entered per team: every member gets the team's points.
    $points = [];
    foreach ($players->slice(0, 2) as $p) {
        $points[$p->id] = 3;
    }
    foreach ($players->slice(2, 2) as $p) {
        $points[$p->id] = 8;
    }
    roundWithPoints($tournament, $points);

    $pivots = $tournament->usersWithScores()->get()->keyBy('id');

    expect((int) $pivots[$players[0]->id]->pivot->team_score)->toBe(3);
    expect((int) $pivots[$players[2]->id]->pivot->team_score)->toBe(8);
    expect((int) $pivots[$players[2]->id]->pivot->ranking)->toBe(1);
    expect((int) $pivots[$players[0]->id]->pivot->ranking)->toBe(2);
});

test('deleting a round removes its points from the totals', function () {
    $tournament = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $player = User::factory()->create();
    $tournament->usersWithScores()->attach($player->id, ['score' => 0]);

    roundWithPoints($tournament, [$player->id => 10]);
    $second = roundWithPoints($tournament, [$player->id => 7]);

    $second->delete();
    $tournament->applyRoundTotals();

    expect((int) $tournament->usersWithScores()->first()->pivot->score)->toBe(10);
});

test('round numbers count up per tournament', function () {
    $tournament = Tournament::factory()->create();
    $other = Tournament::factory()->create();

    $tournament->rounds()->create(['number' => $tournament->nextRoundNumber()]);
    $tournament->rounds()->create(['number' => $tournament->nextRoundNumber()]);

    expect($tournament->rounds()->pluck('number')->all())->toBe([1, 2]);
    expect($other->nextRoundNumber())->toBe(1);
});
