<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Tag;
use App\Support\CurrentEdition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    public function index(CurrentEdition $current): View
    {
        $edition = $current->get();

        $query = Game::query();

        // Only games scheduled in the edition currently being viewed.
        if ($edition) {
            $query->whereHas('schedules', fn($q) => $q->where('schedules.edition_id', $edition->id));
        }

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%');
        }

        if (request('tags')) {
            $tagIds = is_array(request('tags')) ? request('tags') : [request('tags')];
            $query->whereHas(
                'tags',
                fn($q) =>
                $q->whereIn('tags.id', $tagIds)
            );
        }

        $games = $query->withCount('likedByUsers')
            ->with(['likedByUsers' => fn($q) => $q->where('user_id', Auth::id())])
            ->with('tags')
            ->paginate(5)
            ->appends(request()->query());

        $availableTags = Tag::forModel(Game::class)
            ->whereHas('games')
            ->orderBy('name')
            ->get();

        return view('games.index', [
            'games' => $games,
            'availableTags' => $availableTags,
            'selectedTags' => request('tags', []),
            'edition' => $edition,
        ]);
    }

    public function show(Game $game): View
    {
        return view('games.show', [
            'game' => $game
        ]);
    }
}
