<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\News;
use App\Models\Photo;
use App\Models\Signup;
use App\Models\Tournament;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * The edition archive: a list of all editions and a read-only recap page per
 * archived edition, rendered in that edition's own accent colors.
 */
class EditionController extends Controller
{
    public function index(): View
    {
        return view('editions.index', [
            'editions' => Edition::orderByDesc('year')->get(),
        ]);
    }

    public function show(Edition $edition): View|RedirectResponse
    {
        // The live site IS the active edition; its recap is the site itself.
        if ($edition->is_active) {
            return redirect()->route('schedule');
        }

        return view('editions.show', [
            'edition' => $edition,
            'tournaments' => Tournament::forEdition($edition)->with('game')->get(),
            'photos' => Photo::forEdition($edition)->with('user')->latest()->get(),
            'news' => News::forEdition($edition)->published()->orderByDesc('published_at')->get(),
            'gameResults' => GameResult::forEdition($edition)
                ->where('completed', true)
                ->with('user:id,name,profile_photo_path')
                ->orderBy('catches')
                ->orderByRaw('time_ms IS NULL')
                ->orderBy('time_ms')
                ->take(10)
                ->get(),
            'signupCount' => Signup::forEdition($edition)->where('confirmed', true)->count(),
        ]);
    }
}
