<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EditionController extends Controller
{
    public function index(): View
    {
        $editions = Edition::all();
        return view('edition.index', compact('editions'));
    }

    public function show(string $slug): View
    {
        return view('edition.show', [
            'edition' => Edition::where('slug', $slug)->firstOrFail()
        ]);
    }

    public function signup(string $slug): View
    {
        $edition = Edition::where('slug', $slug)->firstOrFail();
        $this->authorize('signup', $edition);
        return view('edition.signup', ['edition' => $edition]);
    }

    public function signout(string $slug): View
    {
        $edition = Edition::where('slug', $slug)->firstOrFail();
        $this->authorize('signout', $edition);

        $user = Auth::user();
        $user->signups()->where('edition_id', $edition->id)->delete();

        $tournamentIds = Tournament::whereHas('schedule', fn($query) => $query->where('edition_id', $edition->id))->pluck('id');

        $user->tournaments()->detach($tournamentIds);

        session()->flash('success', 'You have successfully signed out of the edition.');
        return view('edition.signup', ['edition' => $edition]);
    }
}
