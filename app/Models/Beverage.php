<?php

namespace App\Models;

use App\Models\Concerns\OptimizesImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Beverage extends Model
{
    use HasFactory, OptimizesImages;

    protected $fillable = [
        'name',
        'description',
        'contains_alcohol',
        'image'
    ];


    public function signups(): BelongsToMany
    {
        return $this->belongsToMany(Signup::class, "signup_beverages");
    }
}
