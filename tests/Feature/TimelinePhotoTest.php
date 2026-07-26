<?php

use App\Livewire\Timeline\Feed;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a registered user can post a photo to the timeline', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    Livewire::test(Feed::class)
        ->set('photo', UploadedFile::fake()->image('lan.jpg', 800, 600))
        ->set('story', 'Wat een avond bij MBLAN')
        ->call('save')
        ->assertHasNoErrors();

    $photo = Photo::first();

    expect($photo)->not->toBeNull()
        ->and($photo->user_id)->toBe($user->id)
        ->and($photo->story)->toBe('Wat een avond bij MBLAN');

    Storage::disk('public')->assertExists($photo->image);
});

test('both a photo and a story are required', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    Livewire::test(Feed::class)
        ->set('story', '')
        ->call('save')
        ->assertHasErrors(['photo', 'story']);

    expect(Photo::count())->toBe(0);
});

test('large uploads are downscaled so the disk stays lean', function () {
    Storage::fake('public');
    $user = User::factory()->create(['email_verified_at' => now()]);
    $this->actingAs($user);

    // Kept modest on purpose: a truecolor bitmap is ~4 bytes/pixel, so a huge
    // fixture would exhaust the test memory limit before our code even runs.
    // 2400px on the long side is still above the 1920 cap, so it must shrink.
    Livewire::test(Feed::class)
        ->set('photo', UploadedFile::fake()->image('huge.jpg', 2400, 1600))
        ->set('story', 'Grote foto')
        ->call('save')
        ->assertHasNoErrors();

    $photo = Photo::first();
    [$width, $height] = getimagesize(Storage::disk('public')->path($photo->image));

    expect(max($width, $height))->toBeLessThanOrEqual(1920)
        ->and(max($width, $height))->toBeGreaterThan(1000);
});
