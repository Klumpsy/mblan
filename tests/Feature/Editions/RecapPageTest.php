<?php

use App\Models\Edition;
use App\Models\GameResult;
use App\Models\News;
use App\Models\Photo;
use App\Models\Tournament;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    // MBLAN25 is seeded as an archived edition by the migrations.
    $this->old = Edition::where('slug', 'mblan25')->firstOrFail();
});

it('lists all editions with the active one marked', function () {
    $this->actingAs($this->user)->get('/edities')
        ->assertOk()
        ->assertSee('MBLAN26')
        ->assertSee('MBLAN25')
        ->assertSee('Huidige editie');
});

it('shows an archived edition recap with its data', function () {
    $tournament = Tournament::factory()->create(['name' => 'Retro Kart Cup', 'edition_id' => $this->old->id, 'concluded' => true]);
    $winner = User::factory()->create(['name' => 'Winnaar Wim']);
    $tournament->usersWithScores()->attach($winner->id, ['score' => 10, 'ranking' => 1]);
    Photo::factory()->create(['story' => 'Legendarische nacht', 'edition_id' => $this->old->id]);
    News::factory()->create(['title' => 'Oud maar goud', 'edition_id' => $this->old->id]);
    GameResult::create(['user_id' => $winner->id, 'edition_id' => $this->old->id, 'catches' => 2, 'completed' => true, 'time_ms' => 50000]);

    $this->actingAs($this->user)->get('/edities/mblan25')
        ->assertOk()
        ->assertSee('MBLAN25')
        ->assertSee('Retro Kart Cup')
        ->assertSee('Winnaar Wim')
        ->assertSee('Legendarische nacht')
        ->assertSee('Oud maar goud');
});

it('redirects the active edition recap to the live site', function () {
    $this->actingAs($this->user)->get('/edities/mblan26')
        ->assertRedirect(route('schedule'));
});

it('renders the recap in the archived edition colors', function () {
    $this->old->update(['primary_color' => '#ff0000', 'palette' => null]);

    $this->actingAs($this->user)->get('/edities/mblan25')
        ->assertSee('--c-primary-500: 255 0 0', false);
});

it('requires login', function () {
    $this->get('/edities')->assertRedirect(route('login'));
    $this->get('/edities/mblan25')->assertRedirect(route('login'));
});
