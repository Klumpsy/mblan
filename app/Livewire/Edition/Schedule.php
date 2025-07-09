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
                $gameDate = $this->getScheduleDate($game->pivot->start_date);
            }

            $this->activeDate = $gameDate ?? now()->format('Y-m-d');
        }
    }

    public function setActiveDate($date)
    {
        $this->activeDate = $date;
    }

    /**
     * Get the schedule date for a game (just the actual date, no modification)
     */
    private function getScheduleDate($startDate)
    {
        return Carbon::parse($startDate)->format('Y-m-d');
    }



    public function render()
    {
        $dates = collect();
        foreach ($this->edition->schedules as $schedule) {
            foreach ($schedule->games as $game) {
                $date = $this->getScheduleDate($game->pivot->start_date);
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
                    $gameDate = $this->getScheduleDate($game->pivot->start_date);
                    return $gameDate === $this->activeDate;
                });

                if ($gamesForDate->isNotEmpty()) {
                    // Sort games by time: 06:00-23:59 first, then 00:00-05:59 at the end
                    $sortedGames = $gamesForDate->sortBy(function ($game) {
                        $gameDateTime = Carbon::parse($game->pivot->start_date);
                        $hour = $gameDateTime->hour;
                        $minute = $gameDateTime->minute;

                        // If hour is 6-23 (morning to late night), sort normally
                        if ($hour >= 6 && $hour <= 23) {
                            return ($hour * 60) + $minute;
                        }

                        // If hour is 0-5 (after midnight), put at the end
                        if ($hour >= 0 && $hour <= 5) {
                            return 1440 + ($hour * 60) + $minute; // 1440 = 24 * 60 minutes
                        }

                        return ($hour * 60) + $minute; // fallback
                    });

                    $scheduleClone = clone $schedule;
                    $scheduleClone->gamesForDate = $sortedGames;
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
