<?php

use App\Models\Game;

it('can create a game', function () {
    $game = Game::factory()->create();

    expect($game)->toBeInstanceOf(Game::class);
    expect($game->exists)->toBeTrue();
});

it('can update a game', function () {
    $game = Game::factory()->create([
        'name' => 'Old Game Name',
    ]);

    $game->update(['name' => 'Updated Game Name']);

    expect($game->name)->toBe('Updated Game Name');
});
