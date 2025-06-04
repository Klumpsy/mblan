<?php

namespace Tests\Feature\Livewire\Game;

use App\Livewire\Game\Like;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_correct_initial_like_status(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        $user->likedGames()->attach($game->id);

        Livewire::actingAs($user)
            ->test(Like::class, ['game' => $game])
            ->assertSet('likesCount', 1);

        Livewire::test(Like::class, ['game' => $game])
            ->assertSet('likesCount', 1);
    }

    public function test_unauthenticated_user_gets_login_required_event_when_liking_game(): void
    {
        $game = Game::factory()->create();

        Livewire::test(Like::class, ['game' => $game])
            ->call('toggleLike')
            ->assertDispatched('login-required');
    }

    public function test_authenticated_user_can_like_a_game(): void
    {
        $user = User::factory()->create();
        $game = Game::factory()->create();

        Livewire::actingAs($user)
            ->test(Like::class, ['game' => $game])
            ->assertSet('isLiked', false)
            ->assertSet('likesCount', 0)
            ->call('toggleLike')
            ->assertSet('isLiked', true)
            ->assertSet('likesCount', 1);

        $this->assertTrue($game->likedByUsers()->where('user_id', $user->id)->exists());
    }
}
