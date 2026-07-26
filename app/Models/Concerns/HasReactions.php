<?php

namespace App\Models\Concerns;

use App\Models\Reaction;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Gives a model emoji reactions (heart / laugh / goat). Used by timeline photos
 * and news items.
 */
trait HasReactions
{
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}
