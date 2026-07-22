<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
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

    public function render()
    {
        $tournament = $this->tournament->fresh();

        $rows = $tournament->getLeaderboard();

        return view('livewire.tournament.ladder', [
            't' => $tournament,
            'rows' => $rows,
            'podium' => $rows->take(3),
            'rest' => $rows->slice(3),
            'topScore' => max(1, (int) $rows->max('score')),
            'scoreLabel' => $tournament->scoreLabel(),
        ]);
    }
}
