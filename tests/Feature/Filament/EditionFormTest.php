<?php

use App\Filament\Resources\EditionResource\Pages\CreateEdition;
use App\Models\Edition;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('edition create form submits without errors', function () {
    Livewire::test(CreateEdition::class)
        ->fillForm([
            'name' => 'Test Edition 2026',
            'slug' => 'test-edition-2026',
            'year' => 2026,
            'is_active' => false,
            'description' => 'A test edition with enough description text to satisfy validation rules.',
            'is_exclusive' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Edition::where('slug', 'test-edition-2026')->exists())->toBeTrue();
});
