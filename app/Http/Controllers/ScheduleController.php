<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $editions = Edition::all();
        return view('schedule.index', compact('editions'));
    }

    public function show(string $id): View
    {
        return view('schedule.detail', [
            'edition' => Edition::findOrFail($id)
        ]);
    }
}
