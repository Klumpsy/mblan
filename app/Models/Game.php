<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory, Taggable;

    protected $fillable = [
        'name',
        'year_of_release',
        'text_block_one',
        'text_block_two',
        'text_block_three',
        'short_description',
        'image',
        'link_to_website',
        'link_to_youtube',
        'likes'
    ];

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'game_user_likes')
            ->using(UserGame::class)
            ->withTimestamps();
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'game_schedule')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    public function getLikesCount(): int
    {
        return $this->likedByUsers()->count();
    }
}
