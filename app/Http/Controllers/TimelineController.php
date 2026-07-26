<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class TimelineController extends Controller
{
    public function index(): View
    {
        return view('timeline.index');
    }
}
