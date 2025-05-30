<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $query = Game::query();

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        $games = $query->paginate(5)->withQueryString();

        return view('games.index', [
            'games' => $games
        ]);
    }

    public function show(string $id): View
    {
        return view('games.show', [
            'game' => Game::findOrFail($id)
        ]);
    }
}
