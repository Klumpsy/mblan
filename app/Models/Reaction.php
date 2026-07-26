<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    /**
     * The reactions players can leave, keyed by a short slug stored in the DB.
     * Heart, laughing, and the goat (MBLAN's greatest-of-all-time nod).
     */
    public const EMOJIS = [
        'heart' => '❤️',
        'laugh' => '😂',
        'goat' => '🐐',
    ];

    protected $fillable = ['user_id', 'emoji'];

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
