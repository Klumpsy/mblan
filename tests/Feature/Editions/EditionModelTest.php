<?php

use App\Models\Edition;

it('seeds mblan26 as the active edition via the migration', function () {
    $edition = Edition::current();

    expect($edition)->not->toBeNull()
        ->and($edition->slug)->toBe('mblan26')
        ->and($edition->year)->toBe(2026)
        ->and($edition->is_active)->toBeTrue()
        ->and($edition->palette['500'])->toBe('101 229 154');
});

it('activates one edition and deactivates the rest', function () {
    $next = Edition::factory()->create(['year' => 2027, 'slug' => 'mblan27', 'is_active' => false]);

    $next->activate();

    expect(Edition::current()->id)->toBe($next->id)
        ->and(Edition::where('is_active', true)->count())->toBe(1);
});

it('uses the slug as route key', function () {
    expect((new Edition)->getRouteKeyName())->toBe('slug');
});
