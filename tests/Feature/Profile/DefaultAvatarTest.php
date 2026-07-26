<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user without an uploaded photo gets a stable, unique random avatar', function () {
    $a = User::factory()->create(['profile_photo_path' => null]);
    $b = User::factory()->create(['profile_photo_path' => null]);

    expect($a->profile_photo_url)->toContain('dicebear');
    // Stable across reloads for the same user.
    expect($a->fresh()->profile_photo_url)->toBe($a->profile_photo_url);
    // Different users get different avatars.
    expect($a->profile_photo_url)->not->toBe($b->profile_photo_url);
});

test('an uploaded profile photo overrides the random default', function () {
    $user = User::factory()->create(['profile_photo_path' => 'profile-photos/mine.jpg']);

    expect($user->profile_photo_url)
        ->toContain('mine.jpg')
        ->not->toContain('dicebear');
});
