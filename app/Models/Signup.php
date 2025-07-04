<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Signup extends Model
{
    use HasFactory;

    public const BBQ_COST = 17.50;
    public const PIZZA_COST = 14.00;
    public const TSHIRT_COST = 25.00;
    public const COSTS_PER_DAY = 14.00;

    protected $fillable = [
        'stays_on_campsite',
        'joins_barbecue',
        'joins_pizza',
        'is_vegan',
        'wants_tshirt',
        'tshirt_size',
        'tshirt_text',
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

    public function calculateCost(): float
    {
        $cost = 0;

        if ($this->joins_barbecue) {
            $cost += self::BBQ_COST;
        }

        foreach ($this->schedules as $schedule) {

            if ($schedule->date && \Carbon\Carbon::parse($schedule->date)->isFriday() && $this->joins_pizza) {
                $cost += self::PIZZA_COST;
                break;
            }
        }

        if ($this->wants_tshirt) {
            $cost += self::TSHIRT_COST;
        }

        $cost += $this->schedules->count() * self::COSTS_PER_DAY;

        return $cost;
    }
}
