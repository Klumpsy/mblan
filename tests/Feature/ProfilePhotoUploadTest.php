<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a lightly compressed phone photo above the old 1MB limit is accepted', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    (new UpdateUserProfileInformation())->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('IMG_1234.jpg')->size(1800), // ~1.8 MB, was rejected by max:1024
    ]);

    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

test('an unreasonably large upload is still rejected', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    (new UpdateUserProfileInformation())->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('huge.jpg')->size(5000), // ~5 MB
    ]);
})->throws(ValidationException::class);

test('a user who has not linked Discord can still save their profile', function () {
    // Regression: the discord_id rule lacked "nullable", so a null discord_id
    // failed validation and silently blocked the whole profile save (including
    // the photo) for anyone who logged in without Discord.
    $user = User::factory()->create(['discord_id' => null]);

    (new UpdateUserProfileInformation())->update($user, [
        'name' => 'Nieuwe Naam',
        'email' => $user->email,
        'discord_id' => null,
    ]);

    expect($user->fresh()->name)->toBe('Nieuwe Naam');
});
