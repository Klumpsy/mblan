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
    expect((int) $p[$c->id]->pivot->ranking)->toBe(2); // dense: next score = next rank
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
    expect((int) $p[$u[2]->id]->pivot->ranking)->toBe(2);
});

test('tied players share one podium step with both names above it', function () {
    $t = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $a = User::factory()->create(['name' => 'Sophie Tie']);
    $b = User::factory()->create(['name' => 'Martin Tie']);
    $c = User::factory()->create(['name' => 'Bas Solo']);
    $t->usersWithScores()->attach([
        $a->id => ['score' => 10],
        $b->id => ['score' => 10],
        $c->id => ['score' => 5],
    ]);
    $t->recalculateRankings();

    $html = Livewire::test(Ladder::class, ['tournament' => $t])->html();

    // One shared step 1 carrying both names, and Bas on a visible step 2.
    expect(substr_count($html, 'h-28'))->toBe(1);
    expect(substr_count($html, 'h-20'))->toBe(1);
    expect(substr_count($html, 'h-16'))->toBe(0);
    expect($html)->toContain('Sophie Tie')->toContain('Martin Tie')->toContain('Bas Solo');

    // The tied names sit above the SAME step: both appear before the step-1 block.
    $step1 = strpos($html, 'h-28');
    expect(strpos($html, 'Sophie Tie'))->toBeLessThan($step1);
    expect(strpos($html, 'Martin Tie'))->toBeLessThan($step1);
});

test('a full podium with a tie still shows steps 1, 2 and 3', function () {
    $t = Tournament::factory()->create(['is_team_based' => false, 'higher_is_better' => true]);
    $u = User::factory()->count(4)->create();
    $t->usersWithScores()->attach([
        $u[0]->id => ['score' => 10],
        $u[1]->id => ['score' => 10],
        $u[2]->id => ['score' => 7],
        $u[3]->id => ['score' => 4],
    ]);
    $t->recalculateRankings();

    $html = Livewire::test(Ladder::class, ['tournament' => $t])->html();

    expect(substr_count($html, 'h-28'))->toBe(1); // step 1 (shared by the tie)
    expect(substr_count($html, 'h-20'))->toBe(1); // step 2
    expect(substr_count($html, 'h-16'))->toBe(1); // step 3
});
