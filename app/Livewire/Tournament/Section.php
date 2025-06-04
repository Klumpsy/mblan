<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Section extends Component
{
    public Tournament $tournament;
    public bool $inactive = false;
    public bool $userJoined = false;

    public function mount(Tournament $tournament): void
    {
        $this->tournament = $tournament;
        $this->userJoined = $this->hasJoinedTournament();
    }

    public function signup(): void
    {
        if ($this->userJoined) {
            return;
        }

        Auth::user()->tournamentsWithScores()->attach($this->tournament->id, [
            'score' => 0,
            'ranking' => null,
        ]);

        $this->tournament->updateRankings();
        $this->userJoined = $this->hasJoinedTournament();
        session()->flash('success', 'Successfully signed up for the tournament.');
    }

    public function leave(): void
    {
        if (!$this->userJoined) {
            return;
        }

        Auth::user()->tournamentsWithScores()->detach($this->tournament->id);

        $this->userJoined = $this->hasJoinedTournament();

        session()->flash('success', 'Successfully left the tournament.');
    }

    public function isTournamentJoinable(): bool
    {
        return $this->tournament->hasYetToStart() && !$this->tournament->is_active;
    }

    protected function hasJoinedTournament(): bool
    {
        return Auth::user()
            ->tournamentsWithScores()
            ->where('tournament_id', $this->tournament->id)
            ->exists();
    }

    public function render()
    {
        return view('livewire.tournament.section');
    }
}
