<?php

use App\Models\Edition;
use Illuminate\Support\Carbon;

afterEach(fn () => Carbon::setTestNow());

it('reports none when no start date is set', function () {
    $edition = new Edition(['starts_at' => null, 'ends_at' => null]);

    expect($edition->countdownPhase())->toBe('none');
});

it('reports upcoming before the start date', function () {
    Carbon::setTestNow('2026-08-01 12:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('upcoming');
});

it('reports live within the edition days, including the whole end day', function () {
    Carbon::setTestNow('2026-09-06 22:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('live');
});

it('reports live at or after start when no end date is set', function () {
    Carbon::setTestNow('2026-09-05 09:00:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => null]);

    expect($edition->countdownPhase())->toBe('live');
});

it('reports over after the end day', function () {
    Carbon::setTestNow('2026-09-07 00:30:00');
    $edition = new Edition(['starts_at' => '2026-09-04', 'ends_at' => '2026-09-06']);

    expect($edition->countdownPhase())->toBe('over');
});
