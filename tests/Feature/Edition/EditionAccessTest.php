<?php

use App\Models\Edition;
use App\Models\Schedule;
use App\Models\Signup;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

/*
|--------------------------------------------------------------------------
| Edition access behaviour
|--------------------------------------------------------------------------
|
| Editions are managed in the backend; one is marked active. Access to
| signing up / signing out is governed by the `signup-edition` and
| `signout-edition` gates (AppServiceProvider), plus exclusive-access rules
| on the edition itself.
|
*/

// --- signup-edition gate / signup route --------------------------------

test('a user may sign up for an edition they have access to and have not joined', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false]);

    expect(Gate::forUser($user)->allows('signup-edition', $edition))->toBeTrue();
    $this->actingAs($user)->get(route('editions.signup', $edition->slug))->assertOk();
});

test('a user cannot sign up again for an edition they already joined', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false]);
    Signup::factory()->create(['user_id' => $user->id, 'edition_id' => $edition->id]);

    expect(Gate::forUser($user)->allows('signup-edition', $edition))->toBeFalse();
    $this->actingAs($user)->get(route('editions.signup', $edition->slug))->assertForbidden();
});

test('a user cannot sign up for an exclusive edition they have no access to', function () {
    $outsider = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => true]);

    expect(Gate::forUser($outsider)->allows('signup-edition', $edition))->toBeFalse();
    $this->actingAs($outsider)->get(route('editions.signup', $edition->slug))->assertForbidden();
});

// --- signout ------------------------------------------------------------

test('signing out removes the signup and detaches the editions tournaments', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false]);
    Signup::factory()->create(['user_id' => $user->id, 'edition_id' => $edition->id]);

    $schedule = Schedule::factory()->create(['edition_id' => $edition->id]);
    $tournament = Tournament::factory()->create(['schedule_id' => $schedule->id]);
    $user->tournaments()->attach($tournament);

    $this->actingAs($user)
        ->post(route('editions.signout', $edition->slug))
        ->assertOk()
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('signups', [
        'user_id' => $user->id,
        'edition_id' => $edition->id,
    ]);
    $this->assertDatabaseMissing('tournament_user', [
        'user_id' => $user->id,
        'tournament_id' => $tournament->id,
    ]);
});

test('a user cannot sign out of an edition they never joined', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false]);

    expect(Gate::forUser($user)->allows('signout-edition', $edition))->toBeFalse();
    $this->actingAs($user)->post(route('editions.signout', $edition->slug))->assertForbidden();
});
