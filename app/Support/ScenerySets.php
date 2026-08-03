<?php

namespace App\Support;

/**
 * The pixel-sprite sets an edition can wear as page backdrop (page-scenery).
 * Each set is a public asset directory plus a curated sprite pool the scenery
 * component scatters in the side gutters. Add a directory + pool here and it
 * becomes selectable on the edition in the admin.
 */
class ScenerySets
{
    public const DEFAULT = 'farm';

    /**
     * @return array<string, array{label: string, path: string, pool: array<int, string>}>
     */
    public static function all(): array
    {
        return [
            'farm' => [
                'label' => 'Boerderij (MBLAN26)',
                'path' => 'images/farm',
                'character' => 'tile_0108', // de boer is altijd van de partij

                'pool' => [
                    'tile_0003', 'tile_0015', 'tile_0027', 'tile_0039', 'tile_0078', // trees + bushes
                    'tile_0032', 'tile_0044', 'tile_0068', 'tile_0083', 'tile_0059', 'tile_0047', // crops
                    'tile_0085', 'tile_0076', 'tile_0089', 'tile_0096', 'tile_0097', // props
                    'tile_0108', 'tile_0109', // farmers
                    'tile_0120', 'tile_0121', 'tile_0122', // sheep, cow, chicken
                    'barn', 'arti',
                ],
            ],
            'space' => [
                'label' => 'Ruimte (2027)',
                'path' => 'images/scenery/space',
                'character' => 'astronaut', // de boer, maar dan in de ruimte

                'pool' => [
                    'planet_ring', 'planet_swirl', 'moon',
                    'rocket', 'ufo', 'satellite',
                    'astronaut', 'alien', 'comet', 'star_cluster',
                ],
            ],
        ];
    }

    /** The set for a key, falling back to the default for unknown keys. */
    public static function get(?string $key): array
    {
        return static::all()[$key] ?? static::all()[static::DEFAULT];
    }

    /** Key => label, for the admin picker. */
    public static function options(): array
    {
        return array_map(fn ($set) => $set['label'], static::all());
    }
}
