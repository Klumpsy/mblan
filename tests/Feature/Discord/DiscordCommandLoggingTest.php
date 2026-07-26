<?php

use App\Models\DiscordCommandLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('running a slash command records a usage log row', function () {
    postDiscordInteraction([
        'type' => 2,
        'data' => ['name' => 'schema'],
        'member' => ['user' => ['id' => '123456789']],
    ])->assertOk();

    expect(DiscordCommandLog::where('command', 'schema')->count())->toBe(1);
    expect(DiscordCommandLog::first()->discord_user_id)->toBe('123456789');
});

test('a PING handshake is not logged as a command', function () {
    postDiscordInteraction(['type' => 1])->assertOk();

    expect(DiscordCommandLog::count())->toBe(0);
});
