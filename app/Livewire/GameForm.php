<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Game;

class GameForm extends Component
{
    use WithFileUploads;

    public $name;
    public $description;
    public $year_of_release;
    public $linkToWebsite;
    public $linkToYoutube;
    public $image;

    public function save()
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'year_of_release' => 'nullable|integer|min:1970|max:' . date('Y'),
            'linkToWebsite' => 'nullable|url|max:255',
            'linkToYoutube' => 'nullable|url|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $validated['image'] = $this->image->store('games', 'public');
        }

        Game::create($validated);

        session()->flash('success', 'Game created!');
        return redirect()->route('games');
    }

    public function render()
    {
        return view('livewire.game-form');
    }
}