<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(Game $game): View
    {
        $game->load([
            'likedByUsers',
            'tournaments' => fn ($q) => $q->with('schedule')->orderBy('is_active', 'desc'),
        ]);

        return view('games.show', ['game' => $game]);
    }
}
