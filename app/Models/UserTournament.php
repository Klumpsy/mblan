<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserTournament extends Pivot
{

    protected $table = 'tournament_user';
    protected $fillable = ['user_id', 'tournament_id', 'score', 'ranking', 'created_at', 'updated_at'];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function game()
    {
        return $this->belongsTo(\App\Models\Game::class);
    }
}
