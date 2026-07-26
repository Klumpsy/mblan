<?php

use App\Livewire\Timeline\Feed;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a user can edit their own timeline post', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $user->id, 'story' => 'Oud verhaal']);

    $this->actingAs($user);

    Livewire::test(Feed::class)
        ->call('startEdit', $photo->id)
        ->assertSet('editingId', $photo->id)
        ->set('editStory', 'Nieuw verhaal')
        ->call('saveEdit')
        ->assertHasNoErrors();

    expect($photo->fresh()->story)->toBe('Nieuw verhaal');
});

test('a user cannot edit someone elses post', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $photo = Photo::factory()->create(['user_id' => $owner->id, 'story' => 'Van iemand anders']);

    $this->actingAs($intruder);

    Livewire::test(Feed::class)
        ->call('startEdit', $photo->id)
        ->assertForbidden();

    expect($photo->fresh()->story)->toBe('Van iemand anders');
});

test('an admin can edit any post', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $photo = Photo::factory()->create(['user_id' => $owner->id, 'story' => 'Origineel']);

    $this->actingAs($admin);

    Livewire::test(Feed::class)
        ->call('startEdit', $photo->id)
        ->set('editStory', 'Door de admin aangepast')
        ->call('saveEdit')
        ->assertHasNoErrors();

    expect($photo->fresh()->story)->toBe('Door de admin aangepast');
});
