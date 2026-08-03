<?php

use App\Models\Edition;
use App\Models\User;

it('shows the active edition banner on the schedule page', function () {
    Edition::current()->update(['hero_image_path' => 'editions/banner-26.png']);

    $this->actingAs(User::factory()->create())->get('/schedule')
        ->assertOk()
        ->assertSee('storage/editions/banner-26.png');
});

it('shows no banner block when the active edition has none', function () {
    $this->actingAs(User::factory()->create())->get('/schedule')
        ->assertOk()
        ->assertDontSee('storage/editions/banner-');
});

it('shows edition banners on the editions overview', function () {
    Edition::where('slug', 'mblan25')->firstOrFail()
        ->update(['hero_image_path' => 'editions/banner-25.png']);

    $this->actingAs(User::factory()->create())->get('/edities')
        ->assertOk()
        ->assertSee('storage/editions/banner-25.png');
});
