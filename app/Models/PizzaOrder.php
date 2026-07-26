<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PizzaOrder extends Model
{
    protected $fillable = ['pizza_round_id', 'user_id', 'pizza', 'notes'];

    public function round(): BelongsTo
    {
        return $this->belongsTo(PizzaRound::class, 'pizza_round_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
