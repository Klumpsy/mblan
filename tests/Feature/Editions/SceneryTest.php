<?php

use App\Models\Edition;
use App\Models\User;
use App\Support\ScenerySets;

it('uses the farm scenery by default', function () {
    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/farm/');
});

it('uses the active edition scenery set', function () {
    Edition::current()->update(['scenery_set' => 'space']);

    // The Arti uploader overlay keeps its own farm sprite, so only the
    // scenery sprites are asserted here.
    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/scenery/space/');
});

it('falls back to farm for an unknown set', function () {
    Edition::current()->update(['scenery_set' => 'bestaat-niet']);

    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/farm/');
});

it('prefers an uploaded sprite package over the built-in set', function () {
    Edition::current()->update(['scenery_sprites' => ['editions/scenery/eigen-a.png', 'editions/scenery/eigen-b.png']]);

    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('storage/editions/scenery/eigen-');
});

it('renders the recap backdrop with the archived edition sprites', function () {
    $old = Edition::where('slug', 'mblan25')->firstOrFail();
    $old->update(['scenery_sprites' => ['editions/scenery/retro-a.png']]);

    $this->actingAs(User::factory()->create())->get('/edities/mblan25')
        ->assertOk()
        ->assertSee('storage/editions/scenery/retro-a.png');
});

it('always shows the edition character in the backdrop', function () {
    // Farm: the farmer; space: the astronaut. The character is guaranteed,
    // the other five slots are random.
    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/farm/tile_0108.png');

    Edition::current()->update(['scenery_set' => 'space']);

    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/scenery/space/astronaut.png');
});

it('uses the first uploaded sprite as the always-visible character', function () {
    Edition::current()->update(['scenery_sprites' => [
        'editions/scenery/held.png',
        'editions/scenery/rest-a.png', 'editions/scenery/rest-b.png', 'editions/scenery/rest-c.png',
        'editions/scenery/rest-d.png', 'editions/scenery/rest-e.png', 'editions/scenery/rest-f.png',
        'editions/scenery/rest-g.png', 'editions/scenery/rest-h.png', 'editions/scenery/rest-i.png',
    ]]);

    $this->actingAs(User::factory()->create())->get('/tijdlijn')
        ->assertOk()
        ->assertSee('storage/editions/scenery/held.png');
});

it('themes every decorative sprite on the site with the edition set', function () {
    Edition::current()->update(['scenery_set' => 'space']);
    $user = User::factory()->create();

    // Schedule: diorama + scenery + nav flourish + upload overlay all space.
    $this->actingAs($user)->get('/schedule')
        ->assertOk()
        ->assertSee('images/scenery/space/astronaut.png')
        ->assertDontSee('images/farm/');

    // Timeline: the chase runs with the edition character and mascot.
    $this->actingAs($user)->get('/tijdlijn')
        ->assertOk()
        ->assertSee('images/scenery/space/astronaut.png')
        ->assertSee('images/scenery/space/alien.png')
        ->assertDontSee('images/farm/');

    // Tournaments: mascot, deco and the leaderboard stat icons.
    $this->actingAs($user)->get('/tournaments')
        ->assertOk()
        ->assertDontSee('images/farm/');
});

it('exposes mascot and landmark roles per set and from uploads', function () {
    $edition = Edition::current();

    expect($edition->sceneryMascot())->toContain('images/farm/arti.png')
        ->and($edition->sceneryLandmark())->toContain('images/farm/barn.png');

    $edition->update(['scenery_set' => 'space']);
    expect($edition->fresh()->sceneryMascot())->toContain('space/alien.png')
        ->and($edition->fresh()->sceneryLandmark())->toContain('space/planet_ring.png');

    $edition->update(['scenery_sprites' => ['e/held.png', 'e/maat.png', 'e/gebouw.png']]);
    $fresh = $edition->fresh();
    expect($fresh->sceneryCharacter())->toContain('storage/e/held.png')
        ->and($fresh->sceneryMascot())->toContain('storage/e/maat.png')
        ->and($fresh->sceneryLandmark())->toContain('storage/e/gebouw.png');

    // A one-sprite package falls back to the character for every role.
    $edition->update(['scenery_sprites' => ['e/solo.png']]);
    expect($edition->fresh()->sceneryMascot())->toContain('storage/e/solo.png');
});

it('ships every sprite in the space pool', function () {
    $set = ScenerySets::get('space');

    foreach ($set['pool'] as $sprite) {
        expect(is_file(public_path($set['path'].'/'.$sprite.'.png')))
            ->toBeTrue("missing sprite: {$sprite}");
    }
});
