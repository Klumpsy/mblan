<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

/**
 * Build a fake Socialite Discord user and make Socialite return it.
 */
function fakeDiscordUser(string $id, ?string $email, string $nick = 'Speler'): void
{
    $ghost = new SocialiteUser();
    $ghost->map([
        'id' => $id,
        'nickname' => $nick,
        'name' => $nick,
        'email' => $email,
    ]);

    $provider = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
    $provider->shouldReceive('redirectUrl')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($ghost);

    Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);
}

test('the redirect route sends the user to Discord', function () {
    $provider = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
    $provider->shouldReceive('redirectUrl')->andReturnSelf();
    $provider->shouldReceive('redirect')->andReturn(redirect('https://discord.com/oauth2/authorize'));
    Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);

    $this->get(route('discord.redirect'))->assertRedirect();
});

test('a new Discord user is created, verified and logged in', function () {
    fakeDiscordUser('discord-123', 'nieuw@discord.test', 'ArtiJager');

    $this->get(route('discord.callback'))->assertRedirect(route('schedule'));

    $user = User::where('discord_id', 'discord-123')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('ArtiJager');
    expect($user->email_verified_at)->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});

test('an existing account is linked by email instead of duplicated', function () {
    $existing = User::factory()->create(['email' => 'bestaand@mblan.nl', 'discord_id' => null]);

    fakeDiscordUser('discord-999', 'bestaand@mblan.nl');

    $this->get(route('discord.callback'));

    expect(User::where('email', 'bestaand@mblan.nl')->count())->toBe(1);
    expect($existing->fresh()->discord_id)->toBe('discord-999');
    $this->assertAuthenticatedAs($existing->fresh());
});

test('the live (twitch) page renders for a signed-in user', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->get(route('live'))
        ->assertOk()
        ->assertSee('player.twitch.tv', escape: false)
        ->assertSee('mblan26');
});

test('guests cannot see the live page', function () {
    $this->get(route('live'))->assertRedirect(route('login'));
});
