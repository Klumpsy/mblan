<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use App\Support\CurrentEdition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentController extends Controller
{
    public function index(CurrentEdition $current): View
    {
        $user = Auth::user();
        $edition = $current->get();

        $tournaments = Tournament::with(['schedule.edition', 'game'])
            ->get()
            ->filter(function ($tournament) use ($user, $edition) {
                $tournamentEdition = $tournament->schedule?->edition;

                return $tournamentEdition
                    && (!$edition || $tournamentEdition->id === $edition->id)
                    && $tournamentEdition->hasExclusiveAccess($user);
            });

        // The Arti Game: the hardcoded first tournament. Fewer catches = higher rank.
        $artiLeaderboard = User::where('barn_completed', true)
            ->orderBy('barn_catches')
            ->orderBy('name')
            ->take(20)
            ->get(['id', 'name', 'barn_catches']);

        return view('tournaments.index', [
            'tournaments' => $tournaments,
            'edition' => $edition,
            'artiLeaderboard' => $artiLeaderboard,
        ]);
    }

    public function show()
    {
        throw new NotFoundHttpException();
    }
}
