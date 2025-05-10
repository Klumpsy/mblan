<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function index()
    {
        $games = Game::all();
        return view('games.index', compact('games'));
    }

    public function create()
    {
        return view('games.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'year_of_release' => 'nullable|integer|min:1970|max:' . date('Y'),
            'image' => 'nullable|string|max:255',
            'linkToWebsite' => 'nullable|url|max:255',
            'linkToYoutube' => 'nullable|url|max:255',
        ]);

        Game::create($validated);
        return redirect()->route('games')->with('success', 'Game created!');
    }
}
