<?php

namespace App\Livewire;

use App\Models\Tournament;
use Livewire\Component;

class TournamentEditionFilter extends Component
{
    public $year;
    public $tournaments;

    public function mount()
    {
        $this->year = now()->year;
        $this->loadTournaments();
    }

    public function updatedYear()
    {
        $this->loadTournaments();
    }

    public function loadTournaments()
    {
        $this->tournaments = Tournament::whereHas('schedule.edition', function ($query) {
            if ($this->year) {
                $query->where('year', $this->year);
            }
        })->with('schedule.edition')->get();
    }

    public function render()
    {
        return view('livewire.tournament-edition-filter');
    }
}
