<?php

namespace App\Support;

use App\Models\Edition;
use Illuminate\Support\Facades\Cache;

/**
 * Turns a single picked hex colour into a full Tailwind-style 50→950 shade ramp
 * and resolves the colour of the currently active edition.
 *
 * The picked colour is anchored exactly at shade 500. Lighter shades are mixed
 * toward white, darker shades toward near-black, so any hue produces a pleasant
 * ramp usable for the whole UI.
 */
class ThemeService
{
    public const DEFAULT_COLOR = '#65E59A'; // Forge Green

    private const CACHE_KEY = 'theme.active_edition_color';

    /**
     * Mix ratio per shade. Positive = mix toward white, negative = mix toward black,
     * 0 = the picked colour itself.
     */
    private const RAMP = [
        '50' => 0.92,
        '100' => 0.84,
        '200' => 0.68,
        '300' => 0.48,
        '400' => 0.24,
        '500' => 0.00,
        '600' => -0.12,
        '700' => -0.28,
        '800' => -0.44,
        '900' => -0.60,
        '950' => -0.74,
    ];

    /**
     * Colour of the active edition, cached. Falls back to the default so the
     * site is never unstyled.
     */
    public function activeColor(): string
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $color = Edition::query()->where('is_active', true)->value('color');

            return self::normalizeHex($color) ?? self::DEFAULT_COLOR;
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Full ramp for the active edition, keyed by shade → "r g b" (space separated
     * so it plugs straight into `rgb(var(--c-primary-500) / <alpha-value>)`).
     *
     * @return array<string, string>
     */
    public function activePalette(): array
    {
        return $this->paletteFor($this->activeColor());
    }

    /**
     * @return array<string, string>
     */
    public function paletteFor(string $hex): array
    {
        $base = self::hexToRgb(self::normalizeHex($hex) ?? self::DEFAULT_COLOR);

        $palette = [];
        foreach (self::RAMP as $shade => $ratio) {
            $palette[$shade] = self::channels(self::mix($base, $ratio));
        }

        return $palette;
    }

    /**
     * Mix a colour toward white (ratio > 0) or black (ratio < 0).
     *
     * @param array{0:int,1:int,2:int} $rgb
     * @return array{0:int,1:int,2:int}
     */
    private static function mix(array $rgb, float $ratio): array
    {
        $target = $ratio >= 0 ? 255 : 0;
        $amount = abs($ratio);

        return array_map(
            fn (int $c) => (int) round($c + ($target - $c) * $amount),
            $rgb
        );
    }

    /**
     * @param array{0:int,1:int,2:int} $rgb
     */
    private static function channels(array $rgb): string
    {
        return implode(' ', $rgb);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Normalize to a 6-digit #RRGGBB hex, expanding shorthand. Returns null if invalid.
     */
    public static function normalizeHex(?string $hex): ?string
    {
        if ($hex === null) {
            return null;
        }

        $hex = ltrim(trim($hex), '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return null;
        }

        return '#' . strtolower($hex);
    }
}
