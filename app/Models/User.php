<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'discord_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class);
    }

    public function likedGames(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_user_likes')
            ->using(UserGame::class)
            ->withTimestamps();
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signup::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'author_id');
    }

    public function blogComments(): HasMany
    {
        return $this->hasMany(BlogComment::class, 'author_id');
    }

    public function hasSignedUpFor(Edition $edition): bool
    {
        return $this->signups()->where('edition_id', $edition->id)->exists();
    }

    public function hasSignedUpForLatestEdition(): bool
    {
        $latestEdition = Edition::latest('year')->first();

        if (!$latestEdition) {
            return false;
        }

        return $this->signups()
            ->where('edition_id', $latestEdition->id)
            ->exists();
    }


    public function tournamentsWithScores(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_user')
            ->using(UserTournament::class)
            ->withTimestamps();
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class)
            ->using(UserAchievement::class)
            ->withPivot(['progress', 'achieved_at'])
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canDrinkBeer(): bool
    {
        $currentSignup = $this->getCurrentEditionSignup();

        if (!$currentSignup || !$currentSignup->confirmed) {
            return false;
        }

        if (!$currentSignup->last_beer_at) {
            return true;
        }

        return $currentSignup->last_beer_at->diffInSeconds(now()) >= 60;
    }

    public function getBeerCooldownAttribute(): int
    {
        $currentSignup = $this->getCurrentEditionSignup();

        if (!$currentSignup || !$currentSignup->last_beer_at) {
            return 0;
        }

        return max(0, 60 - $currentSignup->last_beer_at->diffInSeconds(now()));
    }
}
