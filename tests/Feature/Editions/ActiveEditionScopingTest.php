<?php

use App\Livewire\Timeline\Feed;
use App\Models\Edition;
use App\Models\News;
use App\Models\Photo;
use App\Models\Schedule;
use App\Models\Tournament;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->old = Edition::factory()->create();
});

it('hides old-edition schedules from the schedule page', function () {
    Schedule::factory()->create(['name' => 'Huidige dag']);
    Schedule::factory()->create(['name' => 'Oude dag', 'edition_id' => $this->old->id]);

    $this->actingAs($this->user)->get('/schedule')
        ->assertOk()
        ->assertSee('Huidige dag')
        ->assertDontSee('Oude dag');
});

it('hides old-edition tournaments from the tournaments page', function () {
    Tournament::factory()->create(['name' => 'Huidig toernooi']);
    Tournament::factory()->create(['name' => 'Oud toernooi', 'edition_id' => $this->old->id]);

    $this->actingAs($this->user)->get('/tournaments')
        ->assertOk()
        ->assertSee('Huidig toernooi')
        ->assertDontSee('Oud toernooi');
});

it('hides old-edition news from the news index but keeps the permalink', function () {
    News::factory()->create(['title' => 'Vers nieuws']);
    $old = News::factory()->create(['title' => 'Stokoud nieuws', 'edition_id' => $this->old->id]);

    $this->actingAs($this->user)->get('/nieuws')
        ->assertOk()
        ->assertSee('Vers nieuws')
        ->assertDontSee('Stokoud nieuws');

    $this->actingAs($this->user)->get(route('news.show', $old))->assertOk();
});

it('hides old-edition photos from the timeline feed', function () {
    Photo::factory()->create(['story' => 'Verhaal van nu']);
    Photo::factory()->create(['story' => 'Verhaal van vroeger', 'edition_id' => $this->old->id]);

    Livewire::actingAs($this->user)->test(Feed::class)
        ->assertSee('Verhaal van nu')
        ->assertDontSee('Verhaal van vroeger');
});
