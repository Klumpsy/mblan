<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'year_of_release',
        'image',
        'linkToWebsite',
        'linkToYoutube',
        'likes'
    ];

    public function tournament()
    {
        return $this->hasOne(Tournament::class);
    }

    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'game_user_likes');
    }

    protected function shortDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::limit(strip_tags($this->description), 300)
        );
    }

    public function getLikesCountAttribute()
    {
        return $this->likedByUsers()->count();
    }
}
