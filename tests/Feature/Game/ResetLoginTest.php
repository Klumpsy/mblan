<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Password;

uses(RefreshDatabase::class);

test('a user can log in with the password set via reset', function () {
    $user = User::factory()->create([
        'email' => 'reset@mblan.nl',
        'email_verified_at' => now(),
    ]);
    $token = Password::broker()->createToken($user);

    // Perform the reset exactly as the reset form does.
    $this->post('/reset-password', [
        'token' => $token,
        'email' => 'reset@mblan.nl',
        'password' => 'NieuwWachtwoord1!',
        'password_confirmation' => 'NieuwWachtwoord1!',
    ])->assertSessionHasNoErrors();

    auth()->logout();

    // Now log in with the new password.
    $this->post('/login', [
        'email' => 'reset@mblan.nl',
        'password' => 'NieuwWachtwoord1!',
    ]);

    $this->assertAuthenticated();
});
