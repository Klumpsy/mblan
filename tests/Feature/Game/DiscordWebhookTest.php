<?php

use App\Models\User;
use App\Services\DiscordWebhookService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Give the service a webhook URL so it actually attempts to post.
    config(['discord.webhook_url' => 'https://discord.test/webhook']);
    Http::fake(['discord.test/*' => Http::response('', 204)]);
});

test('no webhook is sent when none is configured', function () {
    config(['discord.webhook_url' => null]);

    $sent = (new DiscordWebhookService())->announceArtiRecord(User::factory()->create(), 0, 41000);

    expect($sent)->toBeFalse();
    Http::assertNothingSent();
});

test('the Arti record message is Dutch and free of emoji', function () {
    $user = User::factory()->create(['name' => 'Sanne']);

    (new DiscordWebhookService())->announceArtiRecord($user, 0, 41000);

    Http::assertSent(function ($request) {
        $embed = $request['embeds'][0];
        expect($embed['title'])->toBe('Nieuw record in Het Arti Spel');
        expect($embed['description'])->toContain('Sanne')->toContain('0:41');
        // house style: no emoji anywhere in the payload
        expect(preg_match('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}]/u', json_encode($embed)))->toBe(0);

        return true;
    });
});

test('reaching the barn as the new leader posts an Arti record', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['completed' => true, 'caught' => 0, 'time' => 40000])
        ->assertOk();

    Http::assertSent(fn ($r) => str_contains($r['embeds'][0]['title'], 'Arti'));
});

test('a slower, non-improving run does not post to Discord', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'barn_completed' => true,
        'barn_catches' => 0,
        'barn_time_ms' => 30000,
    ]);

    $this->actingAs($user)
        ->postJson(route('game.sync'), ['completed' => true, 'caught' => 5, 'time' => 99000])
        ->assertOk();

    Http::assertNothingSent();
});
