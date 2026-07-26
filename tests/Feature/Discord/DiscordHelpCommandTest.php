<?php

use App\Support\DiscordCommands;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the /help command lists every registered command', function () {
    $response = postDiscordInteraction(['type' => 2, 'data' => ['name' => 'help']]);

    $response->assertOk();
    expect($response->json('type'))->toBe(4); // CHANNEL_MESSAGE_WITH_SOURCE

    $description = $response->json('data.embeds.0.description');

    // Every command in the catalogue must appear in the help listing.
    foreach (DiscordCommands::all() as $command) {
        expect($description)->toContain('/'.$command['name']);
        expect($description)->toContain($command['description']);
    }
});

test('the /help reply is ephemeral so it does not clutter the channel', function () {
    $response = postDiscordInteraction(['type' => 2, 'data' => ['name' => 'help']]);

    expect($response->json('data.flags'))->toBe(64); // EPHEMERAL
});
