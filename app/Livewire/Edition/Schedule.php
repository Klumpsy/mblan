<?php

namespace App\Livewire\Edition;

use Livewire\Component;
use App\Models\Edition;
use Carbon\Carbon;

class Schedule extends Component
{
    public Edition $edition;
    public $activeDate = null;

    public function mount(Edition $edition)
    {
        $this->edition = $edition->load(['schedules.games.tags']);

        if ($this->edition->schedules->isNotEmpty()) {
            $firstSchedule = $this->edition->schedules->sortBy('date')->first();
            $gameDate = null;

            if ($firstSchedule->games->isNotEmpty()) {
                $game = $firstSchedule->games->first();
                $gameDate = Carbon::parse($game->pivot->start_date)->format('Y-m-d');
            }

            $this->activeDate = $gameDate ?? now()->format('Y-m-d');
        }
    }

    public function setActiveDate($date)
    {
        $this->activeDate = $date;
    }

    public function render()
    {

        $dates = collect();
        foreach ($this->edition->schedules as $schedule) {
            foreach ($schedule->games as $game) {
                $date = Carbon::parse($game->pivot->start_date)->format('Y-m-d');
                if (!$dates->contains($date)) {
                    $dates->push($date);
                }
            }
        }
        $dates = $dates->sort();

        $schedulesForDate = collect();
        if ($this->activeDate) {
            foreach ($this->edition->schedules as $schedule) {
                $gamesForDate = $schedule->games->filter(function ($game) {
                    $gameDate = Carbon::parse($game->pivot->start_date)->format('Y-m-d');
                    return $gameDate === $this->activeDate;
                });

                if ($gamesForDate->isNotEmpty()) {
                    $scheduleClone = clone $schedule;
                    $scheduleClone->gamesForDate = $gamesForDate;
                    $schedulesForDate->push($scheduleClone);
                }
            }
        }

        return view('livewire.edition.schedule', [
            'dates' => $dates,
            'schedulesForDate' => $schedulesForDate,
        ]);
    }
}
