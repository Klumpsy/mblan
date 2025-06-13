<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserAchievement extends Pivot
{
    protected $table = 'achievement_user';
    protected $casts = [
        'achieved_at' => 'datetime',
    ];
}
