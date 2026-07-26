<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function beerInteraction(string $name, ?string $discordId = null, string $displayName = 'Tester'): array
{
    $payload = ['type' => 2, 'data' => ['name' => $name]];
    if ($discordId !== null) {
        $payload['member'] = ['user' => ['id' => $discordId, 'global_name' => $displayName]];
    }

    return $payload;
}

test('/beer adds a beer to the linked user and replies publicly', function () {
    $user = User::factory()->create(['discord_id' => '9001', 'beer_count' => 4]);

    $response = postDiscordInteraction(beerInteraction('beer', '9001', 'Bart'));

    $response->assertOk();
    expect($response->json('type'))->toBe(4);
    expect($response->json('data.flags'))->toBeNull(); // public, not ephemeral
    expect($response->json('data.embeds.0.title'))->toBe('Proost!');

    expect($user->fresh()->beer_count)->toBe(5);
    expect($user->fresh()->last_beer_at)->not->toBeNull();
});

test('/beer message escalates: high counts read differently than low counts', function () {
    // The reply must at least mention the new running total.
    $user = User::factory()->create(['discord_id' => '9002', 'beer_count' => 24]);

    $response = postDiscordInteraction(beerInteraction('beer', '9002', 'Martin'));

    expect($response->json('data.embeds.0.description'))->toContain('25');
});

test('/beer nudges unlinked Discord users to link their account', function () {
    $response = postDiscordInteraction(beerInteraction('beer', '404', 'Vreemde'));

    $response->assertOk();
    expect($response->json('data.flags'))->toBe(64); // ephemeral
    expect($response->json('data.embeds.0.title'))->toBe('Nog geen account gekoppeld');
    expect(User::sum('beer_count'))->toBe(0);
});

test('/beercount reports the grand total across all users', function () {
    User::factory()->create(['beer_count' => 7]);
    User::factory()->create(['beer_count' => 3]);
    User::factory()->create(['beer_count' => 0]);

    $response = postDiscordInteraction(beerInteraction('beercount'));

    $response->assertOk();
    $description = $response->json('data.embeds.0.description');
    expect($description)->toContain('10');       // 7 + 3
    expect($description)->toContain('2 deelnemers'); // only the two with beers
});

test('/beerlist ranks every drinker from most to least', function () {
    User::factory()->create(['name' => 'Piet', 'beer_count' => 2]);
    User::factory()->create(['name' => 'Klaas', 'beer_count' => 9]);
    User::factory()->create(['name' => 'Niemand', 'beer_count' => 0]);

    $response = postDiscordInteraction(beerInteraction('beerlist'));

    $response->assertOk();
    $description = $response->json('data.embeds.0.description');
    expect($description)->toContain('Klaas');
    expect($description)->toContain('Piet');
    expect($description)->not->toContain('Niemand'); // no beers, not listed
    // Klaas (9) must be ranked above Piet (2).
    expect(strpos($description, 'Klaas'))->toBeLessThan(strpos($description, 'Piet'));
});
