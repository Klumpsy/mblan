<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'edition_id',
        'date',
    ];

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_schedule')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
