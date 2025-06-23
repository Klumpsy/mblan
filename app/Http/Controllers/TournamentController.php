<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TournamentController extends Controller
{
    public function index(): View
    {
        $tournaments = Tournament::all()->withRelationshipAutoloading();
        return view('tournaments.index', [
            'tournaments' => $tournaments,
        ]);
    }

    public function show()
    {
        throw new NotFoundHttpException();
    }
}
