<?php

namespace App\Http\Controllers;

use App\Models\Tournament;

class TournamentController extends Controller
{
    public function index()
    {
        $tournaments = Tournament::all()->withRelationshipAutoloading();
        return view('tournaments.index', [
            'tournaments' => $tournaments,
        ]);
    }
}
