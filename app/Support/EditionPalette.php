<?php

namespace App\Support;

/**
 * Derives the 11-step accent palette (--c-primary-50…950) from one base hex
 * color: lighter shades mix toward white, darker shades toward black. Editions
 * can pin an exact palette (editions.palette) to bypass generation entirely.
 */
class EditionPalette
{
    /** Mix ratios per shade: positive mixes white in, negative mixes black in. */
    private const STEPS = [
        '50' => 0.90, '100' => 0.80, '200' => 0.65, '300' => 0.48, '400' => 0.25,
        '500' => 0.0,
        '600' => -0.12, '700' => -0.28, '800' => -0.44, '900' => -0.60, '950' => -0.74,
    ];

    /** @return array<string, string> shade => "r g b" */
    public static function fromBaseColor(string $hex): array
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        $palette = [];
        foreach (self::STEPS as $shade => $mix) {
            $target = $mix >= 0 ? 255 : 0;
            $ratio = abs($mix);

            $palette[$shade] = implode(' ', array_map(
                fn (int $channel) => (int) round($channel + ($target - $channel) * $ratio),
                [$r, $g, $b],
            ));
        }

        return $palette;
    }

    /** @return array{int, int, int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
