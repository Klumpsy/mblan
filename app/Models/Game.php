<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
