<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'yearOfRelease',
        'textBlockOne',
        'textBlockTwo',
        'textBlockThree',
        'shortDescription',
        'image',
        'linkToWebsite',
        'linkToYoutube',
        'likes'
    ];

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'game_user_likes');
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'game_schedule')
            ->withPivot('start_date', 'end_date')
            ->withTimestamps();
    }

    public function getLikesCountAttribute()
    {
        return $this->likedByUsers()->count();
    }
}
