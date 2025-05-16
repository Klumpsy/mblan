<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
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
        return Game::whereHas('schedules', function ($query) {
            $query->where('schedules.edition_id', $this->id);
        });
    }
}
