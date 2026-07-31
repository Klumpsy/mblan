<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Splits players into random teams. Both modes shuffle first, so calling
 * again ("Shuffle teams") produces a fresh line-up. Leftover players are
 * spread over the teams (sizes differ by at most one) — never solo teams.
 */
class TeamGenerator
{
    /**
     * Divide players into exactly $teamCount teams (capped at the number of
     * players, so no empty teams).
     *
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, Collection<int, int>> team number (1-based) => member ids
     */
    public static function byTeamCount(Collection $userIds, int $teamCount): Collection
    {
        $teamCount = max(1, min($teamCount, $userIds->count()));

        return $userIds->shuffle()
            ->values()
            ->groupBy(fn ($id, int $index) => ($index % $teamCount) + 1)
            ->sortKeys();
    }

    /**
     * Divide players into teams of roughly $teamSize. The remainder joins
     * existing teams instead of forming an undersized one.
     *
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, Collection<int, int>> team number (1-based) => member ids
     */
    public static function byTeamSize(Collection $userIds, int $teamSize): Collection
    {
        $teamSize = max(1, $teamSize);
        $teamCount = max(1, intdiv($userIds->count(), $teamSize));

        return self::byTeamCount($userIds, $teamCount);
    }
}
