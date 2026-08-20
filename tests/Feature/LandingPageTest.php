<?php

use function Pest\Laravel\get;
use App\Models\Edition;
use Illuminate\Support\Carbon;

it('serves the Arti in Space game at /spel', function () {
    get('/spel')
        ->assertOk()
        ->assertSee('spaceClassic', escape: false)
        ->assertSee('Arti en de boer, de ruimte in');
});

it('renders the edition wordmark and the Doe mee CTA at /', function () {
    get('/')
        ->assertOk()
        ->assertSee('MBLAN')
        ->assertSee('Doe mee');
});

it('still renders / when there is no active edition', function () {
    Edition::query()->update(['is_active' => false]);

    get('/')
        ->assertOk()
        ->assertSee('Doe mee');
});

it('shows "Datum volgt" when the active edition has no start date', function () {
    Edition::current()->update(['starts_at' => null, 'ends_at' => null]);

    get('/')->assertSee('Datum volgt');
});

it('shows a live countdown when the edition is upcoming', function () {
    Carbon::setTestNow('2026-08-01');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('editionCountdown', escape: false);
});

it('shows a NU BEZIG badge during the edition', function () {
    Carbon::setTestNow('2026-09-05');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('NU BEZIG');
});

it('shows a recap link after the edition is over', function () {
    Carbon::setTestNow('2026-09-10');
    Edition::current()->update(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    get('/')->assertSee('bekijk de recap');
});

it('links to the relocated game at /spel', function () {
    get('/')->assertSee(route('spel'));
});
