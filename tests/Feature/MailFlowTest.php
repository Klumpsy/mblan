<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

/**
 * The account mail flows: a Dutch verification ("welcome") mail on registration,
 * a Dutch password-reset mail, and enforced email verification on the app.
 */

test('registration sends a verification email', function () {
    Notification::fake();

    $this->post('/register', [
        'name' => 'Nieuwe Speler',
        'email' => 'nieuw@mblan.nl',
        'password' => 'wachtwoord123',
        'password_confirmation' => 'wachtwoord123',
        'terms' => false,
    ]);

    $user = User::where('email', 'nieuw@mblan.nl')->first();
    expect($user)->not->toBeNull();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('a password reset request sends a reset email', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('the verification email uses the Dutch MBLAN subject', function () {
    $user = User::factory()->unverified()->create();

    $mail = (new VerifyEmail())->toMail($user);

    expect($mail->subject)->toBe('Bevestig je e-mailadres voor MBLAN26')
        ->and($mail->actionText)->toBe('E-mailadres bevestigen');
});

test('the password reset email uses the Dutch MBLAN subject', function () {
    $user = User::factory()->create();

    $mail = (new ResetPassword('test-token'))->toMail($user);

    expect($mail->subject)->toBe('Wachtwoord opnieuw instellen voor MBLAN26')
        ->and($mail->actionText)->toBe('Wachtwoord opnieuw instellen');
});

test('unverified users cannot reach the app and are sent to verify', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('schedule'))
        ->assertRedirect(route('verification.notice'));
});

test('verified users can reach the app', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    $this->actingAs($user)->get(route('schedule'))->assertOk();
});
