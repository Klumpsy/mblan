<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::with(['games' => fn ($q) => $q->orderByPivot('start_date')])
            ->orderBy('date')
            ->get();

        return view('schedule.index', ['schedules' => $schedules]);
    }
}
