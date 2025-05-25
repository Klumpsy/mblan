<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'edition_id',
        'date',
    ];

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_schedule')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function signups(): BelongsToMany
    {
        return $this->belongsToMany(Signup::class, "signup_schedules");
    }
}
