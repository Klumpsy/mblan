<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'year',
        'slug'
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

    public function loggedInUserHasConfirmedSignup($userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        return $this->confirmedSignups()->where('user_id', $userId)->exists();
    }
}
