<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'model_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model to auto-generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    public function games(): MorphToMany
    {
        return $this->morphedByMany(Game::class, 'taggable');
    }

    public function media(): MorphToMany
    {
        return $this->morphedByMany(Media::class, 'taggable');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
            ->orWhere('description', 'like', '%' . $search . '%');
    }

    public function scopeModelSpecific($query)
    {
        return $query->whereNotNull('model_type');
    }

    public function scopeUniversal($query)
    {
        return $query->whereNull('model_type');
    }

    public function scopeForModel($query, string $modelClass)
    {
        return $query->where(function ($q) use ($modelClass) {
            $q->where('model_type', $modelClass)
                ->orWhereNull('model_type');
        });
    }

    public static function findOrCreateByName(string $name): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name)]
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
