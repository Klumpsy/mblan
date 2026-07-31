<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function wineInteraction(string $name, ?string $discordId = null, string $displayName = 'Tester'): array
{
    $payload = ['type' => 2, 'data' => ['name' => $name]];
    if ($discordId !== null) {
        $payload['member'] = ['user' => ['id' => $discordId, 'global_name' => $displayName]];
    }

    return $payload;
}

test('/wine adds a glass to the linked user and replies publicly', function () {
    $user = User::factory()->create(['discord_id' => '9101', 'wine_count' => 4]);

    $response = postDiscordInteraction(wineInteraction('wine', '9101', 'Bart'));

    $response->assertOk();
    expect($response->json('type'))->toBe(4);
    expect($response->json('data.flags'))->toBeNull(); // public, not ephemeral
    expect($response->json('data.embeds.0.title'))->toBe('Santé!');

    expect($user->fresh()->wine_count)->toBe(5);
    expect($user->fresh()->last_wine_at)->not->toBeNull();
});

test('/wine reply mentions the new personal total', function () {
    $user = User::factory()->create(['discord_id' => '9102', 'wine_count' => 24]);

    $response = postDiscordInteraction(wineInteraction('wine', '9102', 'Martin'));

    expect($response->json('data.embeds.0.description'))->toContain('25');
});

test('/wine does not touch the beer counter', function () {
    $user = User::factory()->create(['discord_id' => '9103', 'beer_count' => 2, 'wine_count' => 0]);

    postDiscordInteraction(wineInteraction('wine', '9103'));

    expect($user->fresh()->beer_count)->toBe(2);
    expect($user->fresh()->wine_count)->toBe(1);
});

test('/wine nudges unlinked Discord users to link their account', function () {
    $response = postDiscordInteraction(wineInteraction('wine', '404', 'Vreemde'));

    $response->assertOk();
    expect($response->json('data.flags'))->toBe(64); // ephemeral
    expect($response->json('data.embeds.0.title'))->toBe('Nog geen account gekoppeld');
    expect((int) User::sum('wine_count'))->toBe(0);
});

test('/winelist ranks every drinker from most to least', function () {
    User::factory()->create(['name' => 'Piet', 'wine_count' => 2]);
    User::factory()->create(['name' => 'Klaas', 'wine_count' => 9]);
    User::factory()->create(['name' => 'Niemand', 'wine_count' => 0]);

    $response = postDiscordInteraction(wineInteraction('winelist'));

    $response->assertOk();
    $description = $response->json('data.embeds.0.description');
    expect($description)->toContain('Klaas');
    expect($description)->toContain('Piet');
    expect($description)->not->toContain('Niemand'); // no wine, not listed
    // Klaas (9) must be ranked above Piet (2).
    expect(strpos($description, 'Klaas'))->toBeLessThan(strpos($description, 'Piet'));
});

test('/wine and /winelist are in the command catalogue for /help and registration', function () {
    $names = collect(\App\Support\DiscordCommands::all())->pluck('name');

    expect($names)->toContain('wine')->toContain('winelist');
});
