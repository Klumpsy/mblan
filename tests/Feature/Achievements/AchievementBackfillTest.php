<?php

use App\Models\Achievement;
use App\Models\Signup;
use App\Models\Tournament;
use App\Models\User;
use App\Services\AchievementEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function unlockedSlugs(User $user): array
{
    return $user->achievements()->wherePivotNotNull('achieved_at')->pluck('slug')->all();
}

test('a confirmed signup unlocks ingeschreven, and camping unlocks kampeerder', function () {
    $user = User::factory()->create();
    Signup::factory()->for($user)->create([
        'confirmed' => true,
        'stays_on_campsite' => true,
        'joins_barbecue' => false,
    ]);

    app(AchievementEvaluator::class)->sync($user);

    expect(unlockedSlugs($user))->toContain('ingeschreven')->toContain('kampeerder');
    expect(unlockedSlugs($user))->not->toContain('bbq-ganger');
});

test('an unconfirmed signup does not count', function () {
    $user = User::factory()->create();
    Signup::factory()->for($user)->create(['confirmed' => false, 'stays_on_campsite' => true]);

    app(AchievementEvaluator::class)->sync($user);

    expect(unlockedSlugs($user))->not->toContain('ingeschreven');
});

test('winning a tournament unlocks the tournament achievement', function () {
    $user = User::factory()->create();
    $tournament = Tournament::factory()->create();
    DB::table('tournament_user')->insert([
        'tournament_id' => $tournament->id,
        'user_id' => $user->id,
        'ranking' => 1,
        'score' => 100,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    app(AchievementEvaluator::class)->sync($user);

    expect(unlockedSlugs($user))->toContain('toernooiwinnaar')->toContain('podiumbeest');
});

test('the backfill command awards achievements to already-registered users silently', function () {
    $user = User::factory()->create(['beer_count' => 0]);
    Signup::factory()->for($user)->create(['confirmed' => true, 'stays_on_campsite' => false, 'joins_barbecue' => true]);

    // No pivot yet — the user has never triggered a sync.
    expect($user->achievements()->count())->toBe(0);

    $this->artisan('achievements:backfill')->assertSuccessful();

    expect(unlockedSlugs($user->fresh()))->toContain('ingeschreven')->toContain('bbq-ganger');
});

test('trouwe-bezoeker is a manual (admin-granted) achievement', function () {
    $a = Achievement::where('slug', 'trouwe-bezoeker')->first();
    expect($a)->not->toBeNull();
    expect($a->type)->toBe('manual');
    expect($a->metric)->toBeNull();
});
