<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEdition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One player's Arti-game result within one edition; latest completed run wins. */
class GameResult extends Model
{
    use BelongsToEdition;

    protected $fillable = ['user_id', 'edition_id', 'catches', 'score', 'completed', 'time_ms'];

    protected $casts = [
        'catches' => 'integer',
        'score' => 'integer',
        'completed' => 'boolean',
        'time_ms' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
