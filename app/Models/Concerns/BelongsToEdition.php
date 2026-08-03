<?php

namespace App\Models\Concerns;

use App\Models\Edition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ties a model to a yearly edition. New records land in the active edition
 * automatically; listing pages scope explicitly with forEdition() so the
 * admin panel and the recap pages can still see every year.
 */
trait BelongsToEdition
{
    public static function bootBelongsToEdition(): void
    {
        static::creating(function ($model) {
            // Only assign when an edition exists; setting the attribute to
            // null would break seed migrations that predate the column.
            if ($model->edition_id === null && ($edition = Edition::current())) {
                $model->edition_id = $edition->id;
            }
        });
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function scopeForEdition(Builder $query, Edition $edition): Builder
    {
        return $query->where($query->qualifyColumn('edition_id'), $edition->id);
    }
}
