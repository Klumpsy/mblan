<?php

use App\Support\DiscordCommands;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Sign an interaction body the way Discord does and POST it to the endpoint,
 * so the VerifyDiscordSignature middleware is exercised for real.
 */
function postInteraction(array $payload): \Illuminate\Testing\TestResponse
{
    $keypair = sodium_crypto_sign_keypair();
    config(['discord.public_key' => sodium_bin2hex(sodium_crypto_sign_publickey($keypair))]);

    $body = json_encode($payload);
    $timestamp = '1700000000';
    $signature = sodium_bin2hex(sodium_crypto_sign_detached(
        $timestamp.$body,
        sodium_crypto_sign_secretkey($keypair),
    ));

    return test()->call('POST', '/discord/interactions', [], [], [], [
        'HTTP_X-Signature-Ed25519' => $signature,
        'HTTP_X-Signature-Timestamp' => $timestamp,
        'CONTENT_TYPE' => 'application/json',
    ], $body);
}

test('the /help command lists every registered command', function () {
    $response = postInteraction(['type' => 2, 'data' => ['name' => 'help']]);

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
    $response = postInteraction(['type' => 2, 'data' => ['name' => 'help']]);

    expect($response->json('data.flags'))->toBe(64); // EPHEMERAL
});
