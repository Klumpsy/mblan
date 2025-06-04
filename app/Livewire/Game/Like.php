<?php

namespace App\Livewire\Game;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Like extends Component
{
    public Game $game;
    public bool $isLiked = false;
    public int $likesCount = 0;

    public function mount(Game $game)
    {
        $this->game = $game;
        $this->loadLikeStatus();
    }

    public function render()
    {
        return view('livewire.game.like');
    }

    public function toggleLike()
    {
        if (!Auth::check()) {
            $this->dispatch('login-required');
            return;
        }

        $user = Auth::user();

        if ($this->isLiked) {
            $user->likedGames()->detach($this->game->id);
            $this->isLiked = false;
        } else {
            $user->likedGames()->attach($this->game->id);
            $this->isLiked = true;
        }

        $this->loadLikeStatus();
    }

    private function loadLikeStatus()
    {
        $this->likesCount = $this->game->likedByUsers()->count();

        if (Auth::check()) {
            $this->isLiked = $this->game->likedByUsers()->where('user_id', Auth::id())->exists();
        }
    }
}
