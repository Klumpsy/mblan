<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'number',
        'name',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(TournamentRoundScore::class);
    }

    public function label(): string
    {
        return $this->name ?: "Ronde {$this->number}";
    }
}
