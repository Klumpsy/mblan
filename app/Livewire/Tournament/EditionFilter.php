<?php

namespace App\Livewire\Tournament;

use App\Models\Edition;
use App\Models\Tournament;
use Livewire\Component;

class EditionFilter extends Component
{
    public $year;
    public $selectOptions = [];
    public $tournaments;

    public function mount()
    {
        $this->year = now()->year;
        $this->selectOptions = Edition::whereHas('schedules.tournaments')
            ->pluck('year')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
        $this->loadTournaments();
    }

    public function updatedYear()
    {
        $this->loadTournaments();
    }

    public function loadTournaments()
    {
        $this->tournaments = Tournament::whereHas('schedule.edition', function ($query) {
            $query->where('year', $this->year);
        })->with('schedule.edition')->get();
    }

    public function render()
    {
        return view('livewire.tournament.edition-filter');
    }
}
