<?php

use App\Filament\Resources\EditionResource\Pages\ListEditions;
use App\Models\Edition;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
});

it('shows editions in the admin list', function () {
    Edition::factory()->create(['slug' => 'mblan27', 'year' => 2027]);

    Livewire::actingAs($this->admin)
        ->test(ListEditions::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Edition::all());
});

it('activates an edition through the table action and deactivates the rest', function () {
    $next = Edition::factory()->create(['slug' => 'mblan27', 'year' => 2027]);

    Livewire::actingAs($this->admin)
        ->test(ListEditions::class)
        ->callTableAction('activate', $next);

    expect(Edition::current()->slug)->toBe('mblan27')
        ->and(Edition::where('is_active', true)->count())->toBe(1);
});

it('defaults the tournaments admin list to the active edition', function () {
    $current = \App\Models\Tournament::factory()->create(['name' => 'Nu']);
    $old = \App\Models\Tournament::factory()->create([
        'name' => 'Vroeger',
        'edition_id' => Edition::where('slug', 'mblan25')->firstOrFail()->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(\App\Filament\Resources\TournamentResource\Pages\ListTournaments::class)
        ->assertCanSeeTableRecords([$current])
        ->assertCanNotSeeTableRecords([$old]);
});

it('hides the activate action for the already active edition', function () {
    $active = Edition::current();

    Livewire::actingAs($this->admin)
        ->test(ListEditions::class)
        ->assertTableActionHidden('activate', $active);
});
