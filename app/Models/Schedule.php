<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'edition_id',
        'start_date',
        'end_date',
    ];

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_schedule');
    }

    public function tournaments()
    {
        return $this->hasMany(Tournament::class);
    }
}
