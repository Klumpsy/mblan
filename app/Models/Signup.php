<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Signup extends Model
{
    use HasFactory;

    protected $fillable = [
        'stays_on_campsite',
        'joins_barbecue',
        'user_id',
        'edition_id',
        'confirmed'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class, 'signup_schedules');
    }

    public function beverages(): BelongsToMany
    {
        return $this->belongsToMany(Beverage::class, "signup_beverages");
    }
}
