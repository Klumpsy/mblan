<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Pivot
{
    protected $table = 'achievement_user';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'progress',
        'achieved_at',
    ];

    protected $casts = [
        'achieved_at' => 'datetime',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }
}
