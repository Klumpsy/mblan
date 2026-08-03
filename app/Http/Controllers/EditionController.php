<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\News;
use App\Models\Photo;
use App\Models\Schedule;
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

        // Deelnemers uit de pivot; oude edities zonder pivot-data vallen
        // terug op de bevestigde aanmeldingen.
        $participantCount = $edition->participants()->count()
            ?: Signup::forEdition($edition)->where('confirmed', true)->count();

        return view('editions.show', [
            'edition' => $edition,
            'participantCount' => $participantCount,
            'participants' => $edition->participants()->orderBy('name')->get(),
            'schedules' => Schedule::forEdition($edition)
                ->with([
                    'games' => fn ($q) => $q->orderByPivot('start_date'),
                    'blocks' => fn ($q) => $q->orderBy('start_date'),
                ])
                ->orderBy('date')
                ->get(),
            'tournaments' => Tournament::forEdition($edition)->with('game')->get(),
            'photos' => Photo::forEdition($edition)->with('user')->latest()->get(),
            'news' => News::forEdition($edition)->published()->orderByDesc('published_at')->get(),
            'gameResults' => GameResult::forEdition($edition)
                ->where('completed', true)
                ->with('user:id,name,profile_photo_path')
                ->orderByRaw('score IS NULL')
                ->orderByDesc('score')
                ->orderBy('catches')
                ->orderByRaw('time_ms IS NULL')
                ->orderBy('time_ms')
                ->take(10)
                ->get(),
        ]);
    }
}
