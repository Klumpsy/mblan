<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Tournament;
use App\Support\CurrentEdition;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EditionController extends Controller
{
    /**
     * The game roster: the current (active) edition's schedule.
     */
    public function schedule(CurrentEdition $current): View
    {
        $edition = $current->get();
        abort_if($edition === null, 404);

        return view('schedule.index', ['edition' => $edition]);
    }

    public function signup(Edition $edition): View
    {
        return view('edition.signup', ['edition' => $edition]);
    }

    public function signout(Edition $edition): View
    {
        $user = Auth::user();
        $user->signups()->where('edition_id', $edition->id)->delete();

        $tournamentIds = Tournament::whereHas('schedule', fn($query) => $query->where('edition_id', $edition->id))->pluck('id');
        $user->tournaments()->detach($tournamentIds);

        session()->flash('success', 'You have successfully signed out of the edition.');
        return view('edition.signup', ['edition' => $edition]);
    }
}
