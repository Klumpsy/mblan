<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = Schedule::all();
        return view('schedule.index', compact('schedules'));
    }
}
