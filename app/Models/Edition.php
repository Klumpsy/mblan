<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'year',
        'is_active',
        'is_exclusive',
        'slug'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_exclusive' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signup::class);
    }

    public function games(): Builder
    {
        return Game::whereHas('schedules', function ($query) {
            $query->where('schedules.edition_id', $this->id);
        });
    }

    public function hasGames(): bool
    {
        return $this->games()->count() > 0;
    }


    public function confirmedSignups(): HasMany
    {
        return $this->hasMany(Signup::class)->where('confirmed', true);
    }

    public function media()
    {
        return $this->hasMany(Media::class);
    }

    public function getBeerLeaderboard()
    {
        return $this->signups()
            ->with('user')
            ->where('confirmed', true)
            ->where('beer_count', '>', 0)
            ->whereHas('user', function ($query) {
                $query->whereNotNull('discord_id');
            })
            ->orderBy('beer_count', 'desc')
            ->orderBy('last_beer_at', 'asc')
            ->get();
    }

    public function getTotalBeersAttribute(): int
    {
        return $this->signups()->sum('beer_count');
    }

    public function exclusiveUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'edition_user_exclusive', 'edition_id', 'user_id')
            ->withTimestamps();
    }

    public function isExclusive(): bool
    {
        return $this->is_exclusive && $this->exclusiveUsers()->count() > 0;
    }

    public function hasExclusiveAccess(User $user): bool
    {
        // If not exclusive, everyone has access
        if (!$this->is_exclusive) {
            return true;
        }

        return $this->exclusiveUsers()->where('user_id', $user->id)->exists();
    }
}
