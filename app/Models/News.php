<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEdition;
use App\Models\Concerns\HasReactions;
use App\Models\Concerns\OptimizesImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class News extends Model
{
    use BelongsToEdition, HasFactory, HasReactions, OptimizesImages;

    protected $table = 'news';

    protected $fillable = [
        'edition_id',
        'title',
        'image',
        'author_id',
        'content',
        'preview_text',
        'slug',
        'published',
        'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // Auto-fill a unique slug from the title when none is set.
        static::saving(function (News $news) {
            if (blank($news->slug)) {
                $news->slug = static::uniqueSlug($news->title);
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'nieuws';
        $slug = $base;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
