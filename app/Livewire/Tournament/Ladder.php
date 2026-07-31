<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * A fancy, real-time tournament ladder. Polls the leaderboard so every visitor
 * sees standings update live as admins enter scores in the backend.
 */
class Ladder extends Component
{
    public Tournament $tournament;

    /** Poll interval; live tournaments refresh faster than concluded ones. */
    public function pollInterval(): string
    {
        return $this->tournament->is_active ? '5s' : '30s';
    }

    /**
     * Sign the current player up for this tournament, or withdraw again.
     * Registration is closed once a tournament is concluded.
     */
    public function toggleRegister(): void
    {
        if (! Auth::check() || $this->tournament->concluded) {
            return;
        }

        $userId = Auth::id();

        if ($this->tournament->registrations()->whereKey($userId)->exists()) {
            $this->tournament->registrations()->detach($userId);
        } else {
            $this->tournament->registrations()->syncWithoutDetaching([$userId]);
        }
    }

    public function render()
    {
        $tournament = $this->tournament->fresh();

        $rows = $tournament->getLeaderboard();

        // The podium holds the top three RANKS; players with the same score
        // share a step, so a tie for first shows both names above step 1.
        $podium = $rows->filter(fn ($row) => $row['ranking'] !== null && $row['ranking'] <= 3)
            ->groupBy('ranking')
            ->sortKeys();

        $registrants = $tournament->registrations()->orderBy('name')->get();

        return view('livewire.tournament.ladder', [
            't' => $tournament,
            'rows' => $rows,
            'podium' => $podium,
            'rest' => $rows->slice($podium->flatten(1)->count()),
            'topScore' => max(1, (int) $rows->max('score')),
            'scoreLabel' => $tournament->scoreLabel(),
            'isRegistered' => Auth::check() && $registrants->contains('id', Auth::id()),
            'registrants' => $registrants,
            'registrationCount' => $registrants->count(),
        ]);
    }
}
