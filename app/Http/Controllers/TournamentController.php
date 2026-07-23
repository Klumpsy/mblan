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

        // The Arti Game: the hardcoded first tournament.
        // Fewer catches ranks higher; ties are broken by the fastest completion time.
        $artiLeaderboard = User::where('barn_completed', true)
            ->orderBy('barn_catches')
            ->orderByRaw('barn_time_ms IS NULL')
            ->orderBy('barn_time_ms')
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'barn_catches', 'barn_time_ms']);

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
