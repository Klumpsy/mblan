<?php

namespace App\Models;

use App\Models\Concerns\OptimizesImages;
use Illuminate\Contracts\Auth\MustVerifyEmail;

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

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use OptimizesImages;
    use TwoFactorAuthenticatable;

    /**
     * Uploaded profile photos are downscaled and recompressed on save. Jetstream
     * stores them on the 'public' disk (config/jetstream.php).
     *
     * @var array<int, string>
     */
    protected array $optimizableImages = ['profile_photo_path'];

    /**
     * A fun, stable, per-user random avatar for players who have not uploaded a
     * photo. Seeded by the account id so it never changes, and every account
     * gets a different one. Uploading a photo on the profile form overrides it.
     */
    public function defaultProfilePhotoUrl(): string
    {
        return 'https://api.dicebear.com/9.x/pixel-art/svg?seed='.urlencode('mblan-'.$this->id);
    }

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
        'barn_catches',
        'barn_completed',
        'barn_time_ms',
        'beer_count',
        'last_beer_at',
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
        'last_beer_at' => 'datetime',
        'beer_count' => 'integer',
    ];

    /**
     * Log one drunk beer and return the new personal total. Used by the Discord
     * /beer command.
     */
    public function drinkBeer(int $amount = 1): int
    {
        $this->increment('beer_count', $amount);
        $this->forceFill(['last_beer_at' => now()])->save();

        return $this->beer_count;
    }

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

    /** Timeline photos this user has posted. */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /** Emoji reactions this user has left on photos/news. */
    public function reactionsGiven(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    public function tournamentsWithScores(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_user')
            ->using(UserTournament::class)
            ->withTimestamps();
    }

    public function tournamentRegistrations(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_registrations')
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

}
