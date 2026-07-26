<?php

namespace App\Models\Concerns;

use App\Support\ImageOptimizer;
use Illuminate\Support\Facades\Log;

/**
 * Optimizes a model's image field(s) whenever a new file is stored, regardless
 * of where the upload came from (Filament, a Livewire form, a seeder). Models
 * may set $optimizableImages (defaults to ['image']) and $imageDisk ('public').
 */
trait OptimizesImages
{
    public static function bootOptimizesImages(): void
    {
        static::saved(function ($model): void {
            $attributes = property_exists($model, 'optimizableImages')
                ? $model->optimizableImages
                : ['image'];

            $disk = property_exists($model, 'imageDisk')
                ? $model->imageDisk
                : 'public';

            foreach ($attributes as $attribute) {
                $value = $model->getAttribute($attribute);

                if (filled($value) && ($model->wasRecentlyCreated || $model->wasChanged($attribute))) {
                    // Optimization must never break a save: if anything goes wrong
                    // we keep the original upload and just log it.
                    try {
                        app(ImageOptimizer::class)->optimize($value, $disk);
                    } catch (\Throwable $e) {
                        Log::warning('Image optimization failed', [
                            'model' => $model::class,
                            'attribute' => $attribute,
                            'path' => $value,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        });
    }
}
