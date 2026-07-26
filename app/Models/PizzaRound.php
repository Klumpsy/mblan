<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PizzaRound extends Model
{
    protected $fillable = ['name', 'is_open'];

    protected $casts = ['is_open' => 'boolean'];

    public function orders(): HasMany
    {
        return $this->hasMany(PizzaOrder::class);
    }

    /** The round players can order in right now, if any. */
    public static function current(): ?self
    {
        return static::where('is_open', true)->latest()->first();
    }
}
