<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

test('a multi-megabyte phone photo is accepted', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    (new UpdateUserProfileInformation())->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('IMG_1234.jpg')->size(4000), // ~4 MB, was rejected by the old 1 MB rule
    ]);

    expect($user->fresh()->profile_photo_path)->not->toBeNull();
});

test('an upload beyond the 12MB ceiling is rejected', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    (new UpdateUserProfileInformation())->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'photo' => UploadedFile::fake()->image('huge.jpg')->size(15000), // ~15 MB
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
