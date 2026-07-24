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
function fakeDiscordUser(string $id, ?string $email, string $nick = 'Speler', bool $emailVerified = true): void
{
    $ghost = new SocialiteUser();
    $ghost->map([
        'id' => $id,
        'nickname' => $nick,
        'name' => $nick,
        'email' => $email,
    ]);
    // Raw Discord payload, where the email-verified flag lives.
    $ghost->user = ['id' => $id, 'email' => $email, 'verified' => $emailVerified];

    $provider = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
    $provider->shouldReceive('redirectUrl')->andReturnSelf();
    $provider->shouldReceive('scopes')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($ghost);

    Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);
}

test('the redirect route sends the user to Discord', function () {
    $provider = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
    $provider->shouldReceive('redirectUrl')->andReturnSelf();
    $provider->shouldReceive('scopes')->andReturnSelf();
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

test('an unverified Discord email cannot take over an existing account', function () {
    $victim = User::factory()->create(['email' => 'slachtoffer@mblan.nl', 'discord_id' => null]);

    // Attacker's Discord reports the victim's email, but Discord has NOT verified it.
    fakeDiscordUser('attacker-discord', 'slachtoffer@mblan.nl', 'Aanvaller', emailVerified: false);

    $this->get(route('discord.callback'));

    // The victim's account must be untouched and never linked to the attacker.
    $victim->refresh();
    expect($victim->discord_id)->toBeNull();

    // The attacker only ever ends up in their own separate account, never the
    // victim's, and never on the victim's email.
    expect(User::where('email', 'slachtoffer@mblan.nl')->count())->toBe(1);
    $attacker = User::where('discord_id', 'attacker-discord')->first();
    expect($attacker->email)->not->toBe('slachtoffer@mblan.nl');
    expect(auth()->id())->not->toBe($victim->id);
    $this->assertAuthenticatedAs($attacker);
});

test('an account already linked to another Discord is not hijacked', function () {
    User::factory()->create(['email' => 'gekoppeld@mblan.nl', 'discord_id' => 'original-discord']);

    fakeDiscordUser('other-discord', 'gekoppeld@mblan.nl', 'Ander');

    $this->get(route('discord.callback'))->assertRedirect(route('login'));

    expect(User::where('email', 'gekoppeld@mblan.nl')->first()->discord_id)->toBe('original-discord');
    $this->assertGuest();
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
