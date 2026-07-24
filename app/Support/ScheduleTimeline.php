<?php

namespace App\Support;

use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Flattens a schedule's games and free-time blocks into one time-ordered list
 * of plain items, each with an absolute start/end. Shared by the Discord
 * reminder, daily-digest and scheduled-event commands so they all read the
 * speelschema the same way.
 */
class ScheduleTimeline
{
    /**
     * @return Collection<int, object{type:string, name:string, start:?Carbon, end:?Carbon, is_tournament:bool, game_id:?int, schedule_name:string}>
     */
    public static function forSchedule(Schedule $schedule): Collection
    {
        $games = $schedule->games->map(fn ($game) => (object) [
            'type' => 'game',
            'name' => $game->name,
            'start' => $game->pivot->start_date ? Carbon::parse($game->pivot->start_date) : null,
            'end' => $game->pivot->end_date ? Carbon::parse($game->pivot->end_date) : null,
            'is_tournament' => (bool) $game->pivot->is_tournament,
            'game_id' => $game->id,
            'schedule_name' => $schedule->name,
        ]);

        $blocks = $schedule->blocks->map(fn ($block) => (object) [
            'type' => 'block',
            'name' => $block->title,
            'start' => $block->start_date ? Carbon::parse($block->start_date) : null,
            'end' => $block->end_date ? Carbon::parse($block->end_date) : null,
            'is_tournament' => false,
            'game_id' => null,
            'schedule_name' => $schedule->name,
        ]);

        return $games->concat($blocks)
            ->sortBy(fn ($item) => $item->start?->timestamp ?? PHP_INT_MAX)
            ->values();
    }
}
