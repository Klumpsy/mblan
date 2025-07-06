<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserGame extends Pivot
{
    protected $table = 'game_user_likes';
    protected $fillable = ['user_id', 'game_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
