<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use Illuminate\View\View;

class EditionController extends Controller
{
    public function index(): View
    {
        $editions = Edition::all();
        return view('edition.index', compact('editions'));
    }

    public function show(string $slug): View
    {
        return view('edition.show', [
            'edition' => Edition::where('slug', $slug)->firstOrFail()
        ]);
    }

    public function signup(string $slug): View
    {
        return view('edition.signup', [
            'edition' => Edition::where('slug', $slug)->firstOrFail()
        ]);
    }
}
