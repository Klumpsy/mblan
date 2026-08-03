<?php

use App\Models\Edition;
use App\Models\News;
use App\Models\Photo;
use App\Models\Schedule;
use App\Models\Signup;
use App\Models\Tournament;

it('auto-fills the current edition on create for every scoped model', function (string $model) {
    $record = $model::factory()->create();

    expect($record->edition_id)->toBe(Edition::current()->id);
})->with([Tournament::class, Schedule::class, Photo::class, News::class, Signup::class]);

it('keeps an explicitly set edition', function () {
    $old = Edition::factory()->create();

    $photo = Photo::factory()->create(['edition_id' => $old->id]);

    expect($photo->edition_id)->toBe($old->id);
});

it('filters with forEdition', function () {
    $old = Edition::factory()->create();
    Photo::factory()->create(['edition_id' => $old->id]);
    Photo::factory()->count(2)->create();

    expect(Photo::forEdition(Edition::current())->count())->toBe(2)
        ->and(Photo::forEdition($old)->count())->toBe(1);
});
