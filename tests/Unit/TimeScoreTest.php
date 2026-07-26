<?php

use App\Support\TimeScore;

test('minutes, seconds and milliseconds combine into total milliseconds', function () {
    expect(TimeScore::toMilliseconds(1, 30, 500))->toBe(90500);
    expect(TimeScore::toMilliseconds(0, 0, 0))->toBe(0);
    expect(TimeScore::toMilliseconds(2, 5, 7))->toBe(125007);
});

test('milliseconds split back into parts', function () {
    expect(TimeScore::toParts(90500))->toBe(['minutes' => 1, 'seconds' => 30, 'milliseconds' => 500]);
    expect(TimeScore::toParts(125007))->toBe(['minutes' => 2, 'seconds' => 5, 'milliseconds' => 7]);
    expect(TimeScore::toParts(0))->toBe(['minutes' => 0, 'seconds' => 0, 'milliseconds' => 0]);
});

test('milliseconds format as m:ss.mmm', function () {
    expect(TimeScore::format(90500))->toBe('1:30.500');
    expect(TimeScore::format(500))->toBe('0:00.500');
    expect(TimeScore::format(125007))->toBe('2:05.007');
});

test('negative input is clamped to zero', function () {
    expect(TimeScore::toMilliseconds(-1, -1, -1))->toBe(0);
    expect(TimeScore::format(-5))->toBe('0:00.000');
});
