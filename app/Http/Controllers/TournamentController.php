<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $tournaments = Tournament::with(['schedule.edition', 'game'])
            ->get()
            ->filter(function ($tournament) use ($user) {
                return $tournament->schedule->edition->hasExclusiveAccess($user);
            });

        return view('tournaments.index', [
            'tournaments' => $tournaments,
        ]);
    }

    public function show()
    {
        throw new NotFoundHttpException();
    }
}
