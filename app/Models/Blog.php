<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Blog extends Model
{
    /** @use HasFactory<\Database\Factories\BlogFactory> */
    use HasFactory, Taggable;

    protected $fillable = [
        'title',
        'image',
        'author_id',
        'content',
        'preview_text',
        'slug',
        'published',
        'published_at',
        'is_featured',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];


    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->orderBy('created_at', 'desc');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
