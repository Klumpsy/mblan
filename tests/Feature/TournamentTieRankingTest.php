<?php

use App\Livewire\Tournament\Ladder;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('players with the same score share the same rank', function () {
    $t = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    [$a, $b, $c] = User::factory()->count(3)->create();
    $t->usersWithScores()->attach([
        $a->id => ['score' => 10],
        $b->id => ['score' => 10],
        $c->id => ['score' => 5],
    ]);

    $t->recalculateRankings();

    $p = $t->usersWithScores()->get()->keyBy('id');
    expect((int) $p[$a->id]->pivot->ranking)->toBe(1);
    expect((int) $p[$b->id]->pivot->ranking)->toBe(1);
    expect((int) $p[$c->id]->pivot->ranking)->toBe(3); // rank 2 is skipped after a tie
});

test('teams with the same score share the same rank', function () {
    $t = Tournament::factory()->create(['is_team_based' => true, 'higher_is_better' => true]);
    $u = User::factory()->count(3)->create();
    $t->usersWithScores()->attach($u[0]->id, ['score' => 8, 'team_score' => 8, 'team_number' => 1, 'team_name' => 'Team 1']);
    $t->usersWithScores()->attach($u[1]->id, ['score' => 8, 'team_score' => 8, 'team_number' => 2, 'team_name' => 'Team 2']);
    $t->usersWithScores()->attach($u[2]->id, ['score' => 3, 'team_score' => 3, 'team_number' => 3, 'team_name' => 'Team 3']);

    $t->recalculateRankings();

    $p = $t->usersWithScores()->get()->keyBy('id');
    expect((int) $p[$u[0]->id]->pivot->ranking)->toBe(1);
    expect((int) $p[$u[1]->id]->pivot->ranking)->toBe(1);
    expect((int) $p[$u[2]->id]->pivot->ranking)->toBe(3);
});

test('tied players stand on the same podium step on the public ladder', function () {
    $t = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    [$a, $b, $c] = User::factory()->count(3)->create();
    $t->usersWithScores()->attach([
        $a->id => ['score' => 10],
        $b->id => ['score' => 10],
        $c->id => ['score' => 5],
    ]);
    $t->recalculateRankings();

    $html = Livewire::test(Ladder::class, ['tournament' => $t])->html();

    // Both rank-1 players get the tallest step and gold; nobody is shown as #2.
    expect(substr_count($html, 'h-28'))->toBe(2);
    expect(substr_count($html, 'h-20'))->toBe(0);
    expect(substr_count($html, 'h-16'))->toBe(1);
});
