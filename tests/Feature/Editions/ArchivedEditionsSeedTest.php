<?php

use App\Models\Edition;
use App\Models\Tournament;

it('seeds mblan24 and mblan25 as clean archived editions', function (string $slug, int $year) {
    $edition = Edition::where('slug', $slug)->first();

    expect($edition)->not->toBeNull()
        ->and($edition->year)->toBe($year)
        ->and($edition->is_active)->toBeFalse()
        ->and($edition->tournaments()->count())->toBe(0)
        ->and($edition->schedules()->count())->toBe(0);
})->with([
    ['mblan24', 2024],
    ['mblan25', 2025],
]);

it('keeps mblan26 the active edition after seeding the archive', function () {
    expect(Edition::current()->slug)->toBe('mblan26');
});

it('exposes content relations for backfilling per edition', function () {
    $old = Edition::where('slug', 'mblan25')->first();
    Tournament::factory()->create(['edition_id' => $old->id]);

    expect($old->tournaments()->count())->toBe(1);
});
