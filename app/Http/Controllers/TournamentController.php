<?php

namespace App\Http\Controllers;

class TournamentController extends Controller
{
    public function index()
    {
        return view('tournaments.index');
    }
}
