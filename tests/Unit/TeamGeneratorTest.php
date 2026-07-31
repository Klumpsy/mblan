<?php

use App\Support\TeamGenerator;

test('splitting by number of teams deals everyone into evenly sized teams', function () {
    $teams = TeamGenerator::byTeamCount(collect(range(1, 8)), 3);

    expect($teams)->toHaveCount(3);
    expect($teams->map->count()->sort()->values()->all())->toBe([2, 3, 3]);
    expect($teams->flatten()->sort()->values()->all())->toBe(range(1, 8));
});

test('splitting by team size spreads the remainder instead of making solo teams', function () {
    $teams = TeamGenerator::byTeamSize(collect(range(1, 7)), 2);

    // 7 players in teams of 2 -> 3 teams of 2, the leftover joins a team (3+2+2).
    expect($teams)->toHaveCount(3);
    expect($teams->map->count()->sort()->values()->all())->toBe([2, 2, 3]);
    expect($teams->flatten()->sort()->values()->all())->toBe(range(1, 7));
});

test('fewer players than the team size still forms one team', function () {
    $teams = TeamGenerator::byTeamSize(collect([1, 2, 3]), 5);

    expect($teams)->toHaveCount(1);
    expect($teams->first()->sort()->values()->all())->toBe([1, 2, 3]);
});

test('more teams than players caps the number of teams at the player count', function () {
    $teams = TeamGenerator::byTeamCount(collect([1, 2]), 5);

    expect($teams)->toHaveCount(2);
    expect($teams->map->count()->all())->each->toBe(1);
});

test('teams are numbered from one', function () {
    $teams = TeamGenerator::byTeamCount(collect(range(1, 4)), 2);

    expect($teams->keys()->all())->toBe([1, 2]);
});

test('generating twice reshuffles the players', function () {
    $players = collect(range(1, 100));

    $first = TeamGenerator::byTeamCount($players, 2)->map(fn ($team) => $team->values()->all());
    $second = TeamGenerator::byTeamCount($players, 2)->map(fn ($team) => $team->values()->all());

    // With 100 players the odds of two identical shuffles are effectively zero.
    expect($first->all())->not->toBe($second->all());
});
