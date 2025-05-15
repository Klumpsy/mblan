<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(): View
    {
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    public function show(string $id): View
    {
        return view('games.detail', [
            'game' => Game::findOrFail($id)
        ]);
    }
}
