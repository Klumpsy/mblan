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
| The mblan has a new edition each year. Participants sign up per edition.
| - Public (non-exclusive) editions are visible to everyone.
| - Past editions are locked down by marking them "exclusive" and attaching
|   the users who took part (exclusiveUsers). Only those users may view them.
| - A user who did NOT take part in a past, exclusive edition cannot see it.
| - Users can sign up for editions they have access to and have not yet
|   joined, and can sign out again.
|
| These rules live in the `view-edition` / `signup-edition` gates
| (AppServiceProvider), the EditionController index, and the EditionPolicy.
|
*/

// --- index: which editions a user sees ---------------------------------

test('a public edition is listed for any user', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false, 'name' => 'Public LAN']);

    $this->actingAs($user)
        ->get(route('editions'))
        ->assertOk()
        ->assertSee('Public LAN');
});

test('an exclusive edition is listed for a user who took part', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => true, 'name' => 'Members LAN']);
    $edition->exclusiveUsers()->attach($user);

    $this->actingAs($user)
        ->get(route('editions'))
        ->assertOk()
        ->assertSee('Members LAN');
});

test('an exclusive edition is hidden from a user who did not take part', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => true, 'name' => 'Secret LAN']);
    $edition->exclusiveUsers()->attach($member);

    $this->actingAs($outsider)
        ->get(route('editions'))
        ->assertOk()
        ->assertDontSee('Secret LAN');
});

// --- view-edition gate / show route ------------------------------------

test('any user may view a public edition', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => false]);

    expect(Gate::forUser($user)->allows('view-edition', $edition))->toBeTrue();
    $this->actingAs($user)->get(route('editions.show', $edition->slug))->assertOk();
});

test('a participating user may view an exclusive edition', function () {
    $user = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => true]);
    $edition->exclusiveUsers()->attach($user);

    expect(Gate::forUser($user)->allows('view-edition', $edition))->toBeTrue();
    $this->actingAs($user)->get(route('editions.show', $edition->slug))->assertOk();
});

test('a non-participating user is forbidden from viewing an exclusive edition', function () {
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    $edition = Edition::factory()->create(['is_exclusive' => true]);
    $edition->exclusiveUsers()->attach($member);

    expect(Gate::forUser($outsider)->allows('view-edition', $edition))->toBeFalse();
    $this->actingAs($outsider)->get(route('editions.show', $edition->slug))->assertForbidden();
});

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
