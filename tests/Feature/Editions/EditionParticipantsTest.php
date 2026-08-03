<?php

use App\Filament\Resources\EditionResource\Pages\EditEdition;
use App\Filament\Resources\EditionResource\RelationManagers\ParticipantsRelationManager;
use App\Models\Edition;
use App\Models\Signup;
use App\Models\User;
use App\Support\AchievementMetrics;
use Livewire\Livewire;

beforeEach(function () {
    $this->old = Edition::where('slug', 'mblan25')->firstOrFail();
});

it('attaches users to editions in both directions', function () {
    $user = User::factory()->create();

    $this->old->participants()->attach($user);

    expect($this->old->participants()->count())->toBe(1)
        ->and($user->editions()->count())->toBe(1)
        ->and($user->editions()->first()->slug)->toBe('mblan25');
});

it('attaches the user to the edition when a signup is confirmed', function () {
    $user = User::factory()->create();

    $signup = Signup::factory()->create(['user_id' => $user->id, 'confirmed' => false]);
    expect($user->editions()->count())->toBe(0);

    $signup->update(['confirmed' => true]);

    expect($user->fresh()->editions()->pluck('slug')->all())->toBe(['mblan26']);
});

it('attaches immediately when a signup is created already confirmed', function () {
    $user = User::factory()->create();

    Signup::factory()->create(['user_id' => $user->id, 'confirmed' => true]);

    expect($user->editions()->pluck('slug')->all())->toBe(['mblan26']);
});

it('lets an admin attach and detach participants on an edition', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $player = User::factory()->create(['name' => 'Retro Speler']);

    Livewire::actingAs($admin)
        ->test(ParticipantsRelationManager::class, [
            'ownerRecord' => $this->old,
            'pageClass' => EditEdition::class,
        ])
        ->callTableAction('attach', data: ['recordId' => $player->id]);

    expect($this->old->participants()->pluck('users.id')->all())->toBe([$player->id]);
});

it('attaches multiple participants in one go', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $players = User::factory()->count(3)->create();

    Livewire::actingAs($admin)
        ->test(ParticipantsRelationManager::class, [
            'ownerRecord' => $this->old,
            'pageClass' => EditEdition::class,
        ])
        ->callTableAction('attach', data: ['recordId' => $players->pluck('id')->all()]);

    expect($this->old->participants()->count())->toBe(3);
});

it('shows the participant count on the recap', function () {
    $this->old->participants()->attach(User::factory()->count(3)->create());

    $this->actingAs(User::factory()->create())->get('/edities/mblan25')
        ->assertOk()
        ->assertViewHas('participantCount', 3);
});

it('offers an editions-attended achievement metric', function () {
    $user = User::factory()->create();
    $user->editions()->attach([$this->old->id, Edition::current()->id]);

    expect(AchievementMetrics::has('editions_attended'))->toBeTrue()
        ->and(AchievementMetrics::value($user, 'editions_attended'))->toBe(2);
});
