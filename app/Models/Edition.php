<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A yearly MBLAN edition. The single active edition is what the live site
 * shows; every other edition is a browsable archive with its own recap page
 * and accent colors.
 */
class Edition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'year', 'slug', 'is_active', 'primary_color', 'palette',
        'logo_path', 'hero_image_path', 'tagline', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
        'palette' => 'array',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    /** The single active edition the live site shows. */
    public static function current(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** Make this the active edition; there is always exactly one. */
    public function activate(): void
    {
        static::query()->whereKeyNot($this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
