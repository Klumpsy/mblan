<?php

use App\Filament\Widgets\ArtiLeaderboardWidget;
use App\Filament\Widgets\DiscordCommandUsageWidget;
use App\Filament\Widgets\PartyPulseWidget;
use App\Filament\Widgets\SignupsOverTimeWidget;
use App\Models\DiscordCommandLog;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

test('the party pulse widget renders with the current counts', function () {
    User::factory()->count(3)->create();
    Signup::factory()->count(2)->create();

    Livewire::test(PartyPulseWidget::class)
        ->assertOk()
        ->assertSee('Spelers')
        ->assertSee('Aanmeldingen');
});

test('the command usage widget renders and reflects logged commands', function () {
    DiscordCommandLog::create(['command' => 'schema']);
    DiscordCommandLog::create(['command' => 'schema']);
    DiscordCommandLog::create(['command' => 'help']);

    $widget = new DiscordCommandUsageWidget();
    $data = (fn () => $this->getData())->call($widget);

    expect($data['labels'])->toContain('/schema');
    expect(array_sum($data['datasets'][0]['data']))->toBe(3);

    Livewire::test(DiscordCommandUsageWidget::class)->assertOk();
});

test('the signups-over-time widget renders', function () {
    Signup::factory()->create();

    Livewire::test(SignupsOverTimeWidget::class)->assertOk();
});

test('the Arti leaderboard widget lists finishers fastest first', function () {
    User::factory()->create(['name' => 'Traag', 'barn_completed' => true, 'barn_time_ms' => 90000, 'barn_catches' => 3]);
    User::factory()->create(['name' => 'Snel', 'barn_completed' => true, 'barn_time_ms' => 40000, 'barn_catches' => 0]);
    User::factory()->create(['name' => 'NietKlaar', 'barn_completed' => false]);

    Livewire::test(ArtiLeaderboardWidget::class)
        ->assertOk()
        ->assertSee('Snel')
        ->assertSee('Traag')
        ->assertDontSee('NietKlaar');
});
