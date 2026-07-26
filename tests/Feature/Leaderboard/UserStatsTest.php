<?php

use App\Models\Photo;
use App\Models\User;
use App\Support\UserLeaderboards;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bracket(array $all, string $key): array
{
    return collect($all)->firstWhere('key', $key);
}

test('beer bracket ranks users by beer_count and skips zero', function () {
    $heavy = User::factory()->create(['beer_count' => 12]);
    $light = User::factory()->create(['beer_count' => 3]);
    User::factory()->create(['beer_count' => 0]);

    $rows = bracket((new UserLeaderboards)->all(), 'beer')['rows'];

    expect($rows)->toHaveCount(2);
    expect($rows[0]['user']->id)->toBe($heavy->id);
    expect($rows[0]['value'])->toBe(12);
    expect($rows[1]['user']->id)->toBe($light->id);
});

test('likes-on-posts counts reactions others left on a user\'s photos', function () {
    $poster = User::factory()->create();
    $fanA = User::factory()->create();
    $fanB = User::factory()->create();

    $photo = Photo::create(['user_id' => $poster->id, 'image' => 'p.jpg']);
    $photo->reactions()->create(['user_id' => $fanA->id, 'emoji' => 'heart']);
    $photo->reactions()->create(['user_id' => $fanB->id, 'emoji' => 'goat']);

    $rows = bracket((new UserLeaderboards)->all(), 'likes')['rows'];

    expect($rows[0]['user']->id)->toBe($poster->id);
    expect($rows[0]['value'])->toBe(2);
});

test('engagement combines photos, likes received, reactions given and achievements', function () {
    $active = User::factory()->create();
    $lurker = User::factory()->create();

    // active posts two photos and reacts once elsewhere
    Photo::create(['user_id' => $active->id, 'image' => 'a.jpg']);
    Photo::create(['user_id' => $active->id, 'image' => 'b.jpg']);
    $lurkerPhoto = Photo::create(['user_id' => $lurker->id, 'image' => 'c.jpg']);
    $lurkerPhoto->reactions()->create(['user_id' => $active->id, 'emoji' => 'heart']);

    $engagement = bracket((new UserLeaderboards)->all(), 'engagement')['rows'];

    // active: 2 photos*3 + 1 reaction given*1 = 7 ; lurker: 1 photo*3 + 1 like received*2 = 5
    expect($engagement[0]['user']->id)->toBe($active->id);
    expect($engagement[0]['value'])->toBeGreaterThan($engagement[1]['value']);
});

test('the leaderboard page renders both tabs with stat brackets', function () {
    $user = User::factory()->create(['beer_count' => 5]);

    $response = $this->actingAs($user)->get(route('tournaments'));

    $response->assertOk();
    $response->assertSee('User stats', false);
    $response->assertSee('Meeste bier gedronken', false);
    $response->assertSee('Betrokkenheid', false);
});
