<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $query = Game::query();

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $games = $query->withCount('likedByUsers')
            ->with(['likedByUsers' => fn($q) => $q->where('user_id', Auth::id())])
            ->with('tags')
            ->paginate(5);

        return view('games.index', [
            'games' => $games
        ]);
    }

    public function show(Game $game): View
    {
        return view('games.show', [
            'game' => $game
        ]);
    }
}
