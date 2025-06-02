<?php

use App\Livewire\TournamentEditionFilter;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(TournamentEditionFilter::class)
        ->assertStatus(200);
});
