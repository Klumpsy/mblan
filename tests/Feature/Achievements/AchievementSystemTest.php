<?php

use App\Livewire\UserAchievements;
use App\Models\Achievement;
use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('automatic beer achievements unlock at their threshold and track progress', function () {
    $user = User::factory()->create(['beer_count' => 5]);

    app(AchievementEvaluator::class)->sync($user);

    $drink5 = Achievement::where('slug', 'dorstige-smid')->first();   // threshold 5
    $drink30 = Achievement::where('slug', 'bar-legende')->first();    // threshold 30

    $unlocked = $user->achievements()->where('achievements.id', $drink5->id)->first();
    $inProgress = $user->achievements()->where('achievements.id', $drink30->id)->first();

    expect($unlocked->pivot->achieved_at)->not->toBeNull();
    expect($inProgress->pivot->achieved_at)->toBeNull();
    expect((int) $inProgress->pivot->progress)->toBe(5);
});

test('achieved_at is stamped once and not overwritten on later syncs', function () {
    $user = User::factory()->create(['beer_count' => 5]);
    $evaluator = app(AchievementEvaluator::class);

    $evaluator->sync($user);
    $first = $user->achievements()->where('slug', 'dorstige-smid')->first()->pivot->achieved_at;

    $user->update(['beer_count' => 20]);
    $evaluator->sync($user);
    $second = $user->fresh()->achievements()->where('slug', 'dorstige-smid')->first()->pivot->achieved_at;

    expect((string) $second)->toBe((string) $first);
});

test('sync returns only the newly unlocked achievements', function () {
    $user = User::factory()->create(['beer_count' => 1]);
    $evaluator = app(AchievementEvaluator::class);

    $firstRun = $evaluator->sync($user);
    expect(collect($firstRun)->pluck('slug'))->toContain('eerste-biertje');

    $secondRun = $evaluator->sync($user);
    expect($secondRun)->toBe([]); // nothing new the second time
});

test('admins can grant a manual achievement and it is idempotent', function () {
    $user = User::factory()->create();
    $editie = Achievement::where('slug', 'editie-2024')->first();
    $evaluator = app(AchievementEvaluator::class);

    expect($evaluator->grant($user, $editie))->toBeTrue();
    expect($user->achievements()->where('achievements.id', $editie->id)->first()->pivot->achieved_at)->not->toBeNull();

    expect($evaluator->grant($user, $editie))->toBeFalse(); // already had it
});

test('a broken metric never throws and simply awards nothing', function () {
    $user = User::factory()->create(['beer_count' => 5]);
    Achievement::create([
        'name' => 'Kapot', 'slug' => 'kapot', 'type' => 'automatic',
        'metric' => 'does-not-exist', 'threshold' => 3, 'color' => '#fff', 'grayed_color' => '#333',
    ]);

    $unlocked = app(AchievementEvaluator::class)->sync($user);

    expect(collect($unlocked)->pluck('slug'))->not->toContain('kapot');
});

test('the profile achievement wall renders unlocked and locked states', function () {
    $user = User::factory()->create(['beer_count' => 5]);

    Livewire::actingAs($user)
        ->test(UserAchievements::class)
        ->assertOk()
        ->assertSee('Achievements')
        ->assertSee('Dorstige smid')   // unlocked (beer 5)
        ->assertSee('Barlegende');     // locked, shown with progress
});
