<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'time_start',
        'time_end',
        'day',
        'game_id',
        'schedule_id',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'tournament_user');
    }
}
