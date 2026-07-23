<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::with(['schedule', 'game'])->get();

        // The Arti Game: the hardcoded first tournament. Fewer catches = higher rank.
        $artiLeaderboard = User::where('barn_completed', true)
            ->orderBy('barn_catches')
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'barn_catches']);

        return view('tournaments.index', [
            'tournaments' => $tournaments,
            'artiLeaderboard' => $artiLeaderboard,
        ]);
    }

    public function show()
    {
        throw new NotFoundHttpException();
    }
}
