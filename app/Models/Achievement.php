<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon_path',
        'color',
        'grayed_color',
        'type',
        'model_type',
        'threshold',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->using(UserAchievement::class)
            ->withPivot(['progress', 'achieved_at'])
            ->withTimestamps();
    }

    public function isAutomatic(): bool
    {
        return $this->type === 'automatic';
    }

    public function isManual(): bool
    {
        return $this->type === 'manual';
    }

    public function progressFor(User $user): ?int
    {
        return $this->users->firstWhere('id', $user->id)?->pivot->progress;
    }

    public function isUnlockedBy(User $user): bool
    {
        return (bool) $this->users->firstWhere('id', $user->id)?->pivot->achieved_at;
    }
}
