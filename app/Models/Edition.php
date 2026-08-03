<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'scenery_set', 'scenery_sprites',
    ];

    protected $casts = [
        'year' => 'integer',
        'is_active' => 'boolean',
        'palette' => 'array',
        'scenery_sprites' => 'array',
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    /** The single active edition the live site shows. */
    public static function current(): ?self
    {
        // Older seed migrations create scoped models before this table exists
        // on a fresh database; without a table there is no current edition.
        if (! static::tableExists()) {
            return null;
        }

        return static::where('is_active', true)->first();
    }

    private static function tableExists(): bool
    {
        static $exists = false;

        return $exists = $exists || \Illuminate\Support\Facades\Schema::hasTable('editions');
    }

    /** The active edition's name for UI branding (eyebrows, titles). */
    public static function currentName(): string
    {
        return static::current()?->name ?? 'MBLAN';
    }

    /**
     * The active edition's name split into wordmark base and accent suffix,
     * e.g. "MBLAN27" => ["MBLAN", "27"], "MBLAN26.5" => ["MBLAN", "26.5"].
     * The accent is everything from the first digit on, so any naming scheme
     * (26, 2028, 26.5) renders with the year highlighted.
     */
    public static function currentBrand(): array
    {
        preg_match('/^(\D*)(.*)$/u', static::currentName(), $m);

        return [$m[1] !== '' ? $m[1] : 'MBLAN', $m[2] ?? ''];
    }

    /** Make this the active edition; there is always exactly one. */
    public function activate(): void
    {
        static::query()->whereKeyNot($this->id)->update(['is_active' => false]);
        $this->update(['is_active' => true]);
    }

    /**
     * The --c-primary-* declarations for this edition's accent palette: the
     * pinned palette when set, otherwise generated from the base color.
     */
    public function cssVariables(): string
    {
        $palette = $this->palette ?: \App\Support\EditionPalette::fromBaseColor($this->primary_color);

        return collect($palette)
            ->map(fn (string $rgb, string $shade) => "--c-primary-{$shade}: {$rgb};")
            ->implode(' ');
    }

    /**
     * The backdrop sprites for this edition as image URLs: the uploaded
     * sprite package when one is set, otherwise the built-in scenery set.
     *
     * @return array<int, string>
     */
    public function scenerySprites(): array
    {
        if (! empty($this->scenery_sprites)) {
            return array_map(fn (string $path) => asset('storage/'.$path), $this->scenery_sprites);
        }

        $set = \App\Support\ScenerySets::get($this->scenery_set);

        return array_map(fn (string $sprite) => asset($set['path'].'/'.$sprite.'.png'), $set['pool']);
    }

    /**
     * The edition's character sprite (the farmer in 2026, an astronaut in a
     * space theme): always present in the backdrop scatter. For an uploaded
     * package the first sprite is the character.
     */
    public function sceneryCharacter(): ?string
    {
        return $this->sceneryRole(0, 'character');
    }

    /** The mascot (Arti in 2026, the alien in space; upload: second sprite). */
    public function sceneryMascot(): ?string
    {
        return $this->sceneryRole(1, 'mascot');
    }

    /** The landmark (the barn in 2026, a planet in space; upload: third sprite). */
    public function sceneryLandmark(): ?string
    {
        return $this->sceneryRole(2, 'landmark');
    }

    /**
     * Resolve a named sprite role: position N of the uploaded package (small
     * packages fall back to the character), or the built-in set's named sprite.
     */
    private function sceneryRole(int $position, string $role): ?string
    {
        if (! empty($this->scenery_sprites)) {
            $sprites = array_values($this->scenery_sprites);

            return asset('storage/'.($sprites[$position] ?? $sprites[0]));
        }

        $set = \App\Support\ScenerySets::get($this->scenery_set);

        return isset($set[$role])
            ? asset($set['path'].'/'.$set[$role].'.png')
            : null;
    }

    public function tournaments(): HasMany
    {
        return $this->hasMany(Tournament::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function signups(): HasMany
    {
        return $this->hasMany(Signup::class);
    }

    public function gameResults(): HasMany
    {
        return $this->hasMany(GameResult::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
