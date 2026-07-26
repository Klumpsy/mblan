<?php

use App\Livewire\Reactions;
use App\Models\News;
use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('a logged-in user can react and toggle the same reaction off', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $photo])->call('toggle', 'heart');
    expect($photo->reactions()->where('emoji', 'heart')->count())->toBe(1);

    Livewire::test(Reactions::class, ['model' => $photo])->call('toggle', 'heart');
    expect($photo->reactions()->count())->toBe(0);
});

test('a guest is prompted to log in and cannot react', function () {
    $photo = Photo::factory()->create();

    Livewire::test(Reactions::class, ['model' => $photo])
        ->call('toggle', 'heart')
        ->assertDispatched('login-required');

    expect(Reaction::count())->toBe(0);
});

test('reactions also work on news items (polymorphic)', function () {
    $user = User::factory()->create();
    $news = News::factory()->create();
    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $news])->call('toggle', 'goat');

    expect($news->reactions()->where('emoji', 'goat')->count())->toBe(1);
});

test('an unknown emoji is ignored', function () {
    $user = User::factory()->create();
    $photo = Photo::factory()->create();
    $this->actingAs($user);

    Livewire::test(Reactions::class, ['model' => $photo])->call('toggle', 'bogus');

    expect($photo->reactions()->count())->toBe(0);
});

test('counts reflect reactions from multiple users', function () {
    $photo = Photo::factory()->create();
    [$a, $b] = User::factory()->count(2)->create();

    $this->actingAs($a);
    Livewire::test(Reactions::class, ['model' => $photo])->call('toggle', 'laugh');
    $this->actingAs($b);
    Livewire::test(Reactions::class, ['model' => $photo])->call('toggle', 'laugh');

    expect($photo->reactions()->where('emoji', 'laugh')->count())->toBe(2);
});
