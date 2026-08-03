<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Tournament;
use App\Models\User;
use App\Support\UserLeaderboards;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentController extends Controller
{
    public function index(?UserLeaderboards $leaderboards = null): View
    {
        $leaderboards ??= app(UserLeaderboards::class);

        $edition = Edition::current();

        $tournaments = Tournament::with(['schedule', 'game'])
            ->when($edition, fn ($q) => $q->forEdition($edition))
            ->get();

        // De editie-klassieker: score-resultaten (hoogste wint) bovenaan;
        // oude maze-resultaten (minste keer gepakt, snelste tijd) daaronder.
        $artiLeaderboard = $edition
            ? User::query()
                ->join('game_results', 'game_results.user_id', '=', 'users.id')
                ->where('game_results.edition_id', $edition->id)
                ->where('game_results.completed', true)
                ->orderByRaw('game_results.score IS NULL')
                ->orderByDesc('game_results.score')
                ->orderBy('game_results.catches')
                ->orderByRaw('game_results.time_ms IS NULL')
                ->orderBy('game_results.time_ms')
                ->orderBy('users.name')
                ->take(20)
                ->get(['users.id', 'users.name', 'game_results.score as game_score', 'game_results.catches as barn_catches', 'game_results.time_ms as barn_time_ms'])
            : collect();

        return view('tournaments.index', [
            'tournaments' => $tournaments,
            'artiLeaderboard' => $artiLeaderboard,
            'statBrackets' => $leaderboards->all(),
        ]);
    }

    public function show()
    {
        throw new NotFoundHttpException();
    }
}
