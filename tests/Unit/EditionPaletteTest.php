<?php

use App\Support\EditionPalette;

it('keeps the base color as the 500 shade', function () {
    $palette = EditionPalette::fromBaseColor('#65E59A');

    expect($palette['500'])->toBe('101 229 154');
});

it('generates 11 shades from light to dark', function () {
    $palette = EditionPalette::fromBaseColor('#3b82f6');

    expect($palette)->toHaveCount(11)
        ->toHaveKeys(['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950']);

    $lightness = fn (string $rgb) => array_sum(array_map('intval', explode(' ', $rgb)));
    expect($lightness($palette['50']))->toBeGreaterThan($lightness($palette['500']))
        ->and($lightness($palette['500']))->toBeGreaterThan($lightness($palette['950']));
});

it('accepts shorthand hex colors', function () {
    expect(EditionPalette::fromBaseColor('#fff')['500'])->toBe('255 255 255');
});
