<?php

use App\Models\Edition;
use App\Models\User;

it('emits the pinned MBLAN26 palette on every page', function () {
    $this->actingAs(User::factory()->create())->get('/schedule')
        ->assertOk()
        ->assertSee('--c-primary-500: 101 229 154', false);
});

it('generates css variables from the base color when no palette is pinned', function () {
    $edition = Edition::factory()->create(['primary_color' => '#65E59A', 'palette' => null]);

    expect($edition->cssVariables())->toContain('--c-primary-500: 101 229 154;');
});
