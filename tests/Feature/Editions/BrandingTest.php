<?php

use App\Models\Edition;
use App\Models\User;

it('splits the edition name into wordmark base and accent', function (string $name, string $base, string $accent) {
    Edition::current()->update(['name' => $name]);

    expect(Edition::currentBrand())->toBe([$base, $accent]);
})->with([
    ['MBLAN26', 'MBLAN', '26'],
    ['MBLAN27', 'MBLAN', '27'],
    ['MBLAN2028', 'MBLAN', '2028'],
    ['MBLAN26.5', 'MBLAN', '26.5'],
    ['WinterLAN', 'WinterLAN', ''],
]);

it('shows the active edition in the nav wordmark', function () {
    $next = Edition::factory()->create(['name' => 'MBLAN27', 'slug' => 'mblan27', 'year' => 2027]);
    $next->activate();

    // Livewire wraps @if output in [if BLOCK] comment markers, so assert the
    // base and the accent span separately.
    $this->actingAs(User::factory()->create())->get('/schedule')
        ->assertOk()
        ->assertSee('MBLAN')
        ->assertSee('<span class="text-primary-400">27</span>', false);
});

it('groups the during-the-LAN pages in a mega menu', function () {
    $this->actingAs(User::factory()->create())->get('/schedule')
        ->assertOk()
        ->assertSee('Tijdens de LAN')
        ->assertSee(route('news.index'))
        ->assertSee(route('timeline'))
        ->assertSee(route('live'))
        ->assertSee(route('pizza'));
});
