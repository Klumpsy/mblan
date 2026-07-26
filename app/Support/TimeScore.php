<?php

namespace App\Support;

/**
 * Converts between a human "minutes / seconds / milliseconds" time and the raw
 * millisecond integer stored as the score for time-based tournaments, and
 * formats it back for display. Lower milliseconds = faster = better.
 */
class TimeScore
{
    public static function toMilliseconds(int $minutes, int $seconds, int $milliseconds): int
    {
        $total = max(0, $minutes) * 60_000
            + max(0, $seconds) * 1_000
            + max(0, $milliseconds);

        return $total;
    }

    /**
     * @return array{minutes: int, seconds: int, milliseconds: int}
     */
    public static function toParts(int $ms): array
    {
        $ms = max(0, $ms);

        return [
            'minutes' => intdiv($ms, 60_000),
            'seconds' => intdiv($ms % 60_000, 1_000),
            'milliseconds' => $ms % 1_000,
        ];
    }

    public static function format(int $ms): string
    {
        $parts = self::toParts($ms);

        return sprintf('%d:%02d.%03d', $parts['minutes'], $parts['seconds'], $parts['milliseconds']);
    }
}
