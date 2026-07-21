<?php

namespace App\Http\Controllers;

use App\Models\Media;
use App\Support\CurrentEdition;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CurrentEdition $current)
    {
        $edition = $current->get();

        $media = Media::query()
            ->with('tags')
            ->when($edition, fn($q) => $q->where('edition_id', $edition->id))
            ->get();

        return view('media.index', [
            'media' => $media,
            'edition' => $edition,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
}
