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

    public function show(string $id): View
    {
        return view('edition.show', [
            'edition' => Edition::findOrFail($id)
        ]);
    }
}
