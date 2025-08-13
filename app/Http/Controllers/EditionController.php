<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EditionController extends Controller
{
    public function index(): View
    {
        $editions = Edition::all()->withRelationshipAutoloading();
        return view('edition.index', compact('editions'));
    }

    public function show(Edition $edition): View
    {
        $edition->load([
            'schedules.games.tags',
            'signups.user',
            'confirmedSignups.user',
        ]);

        $userIds = $edition->confirmedSignups->pluck('user_id')->merge(
            $edition->signups->pluck('user_id')
        )->unique();
        $users = User::whereIn('id', $userIds)
            ->withCount([
                'achievements as achievements_count' => function ($query) {
                    $query->whereNotNull('achievement_user.achieved_at');
                },
                'tournaments',
                'blogComments',
                'likedGames'
            ])
            ->with([
                'likedGames' => fn($q) => $q
                    ->latest('game_user_likes.created_at')
                    ->take(3)
            ])
            ->get()
            ->keyBy('id');



        $edition->confirmedSignups->each(function ($signup) use ($users) {
            $signup->setRelation('user', $users[$signup->user_id]);
        });

        $featuredGames = $edition->games()
            ->with('tags')
            ->take(10)
            ->get();

        return view('edition.show', [
            'edition' => $edition,
            'featuredGames' => $featuredGames,
        ]);
    }



    public function signup(Edition $edition): View
    {
        $this->authorize('signup', $edition);
        return view('edition.signup', ['edition' => $edition]);
    }

    public function signout(Edition $edition): View
    {
        $this->authorize('signout', $edition);

        $user = Auth::user();
        $user->signups()->where('edition_id', $edition->id)->delete();

        $tournamentIds = Tournament::whereHas('schedule', fn($query) => $query->where('edition_id', $edition->id))->pluck('id');

        $user->tournaments()->detach($tournamentIds);

        session()->flash('success', 'You have successfully signed out of the edition.');
        return view('edition.signup', ['edition' => $edition]);
    }
}
