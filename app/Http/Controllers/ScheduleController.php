<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Models\Schedule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $edition = Edition::current();

        $schedules = Schedule::with([
                'games' => fn ($q) => $q->orderByPivot('start_date'),
                'blocks' => fn ($q) => $q->orderBy('start_date'),
            ])
            ->when($edition, fn ($q) => $q->forEdition($edition))
            ->orderBy('date')
            ->get();

        return view('schedule.index', ['schedules' => $schedules]);
    }
}
