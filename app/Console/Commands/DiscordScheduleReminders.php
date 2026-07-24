<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\DiscordWebhookService;
use App\Support\ScheduleTimeline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Posts a Discord reminder shortly before each schedule item starts. Meant to
 * run every minute; a cache marker per item makes sure each reminder fires only
 * once.
 */
class DiscordScheduleReminders extends Command
{
    protected $signature = 'discord:schedule-reminders';
    protected $description = 'Announce schedule items that are about to start on Discord';

    public function handle(DiscordWebhookService $discord): int
    {
        $lead = max(1, (int) config('discord.reminder_lead_minutes', 15));
        $now = now();
        $window = $now->copy()->addMinutes($lead);

        $schedules = Schedule::with(['games', 'blocks'])->get();
        $sent = 0;

        foreach ($schedules as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                if (! $item->start || $item->start->lt($now) || $item->start->gt($window)) {
                    continue;
                }

                $marker = 'discord:reminder:'.md5($item->type.'|'.$item->name.'|'.$item->start->timestamp);

                // Cache::add is atomic: only the first run in the window wins.
                if (! Cache::add($marker, true, now()->addHours(6))) {
                    continue;
                }

                $url = $item->game_id ? route('games.show', $item->game_id) : null;

                $discord->announceScheduleReminder(
                    $item->name,
                    $item->start,
                    $item->schedule_name,
                    $item->is_tournament,
                    $url,
                );
                $sent++;
            }
        }

        $this->info("Reminders verstuurd: {$sent}.");

        return self::SUCCESS;
    }
}
