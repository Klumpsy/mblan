<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'year',
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function participants()
    {
        return $this->belongsToMany(User::class, 'edition_user');
    }

    public function games()
    {
        return $this->hasManyThrough(Game::class, Schedule::class);
    }
}
