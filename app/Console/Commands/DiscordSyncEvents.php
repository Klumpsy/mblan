<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\DiscordApiService;
use App\Support\ScheduleTimeline;
use Illuminate\Console\Command;

/**
 * Mirrors the speelschema into Discord Guild Scheduled Events, so attendees get
 * Discord's native "interested" list and reminders. Idempotent: events are
 * matched by name, created when missing and patched when their times change.
 * Only future items are synced (Discord rejects events in the past).
 */
class DiscordSyncEvents extends Command
{
    protected $signature = 'discord:sync-events';
    protected $description = 'Create or update Discord scheduled events from the speelschema';

    public function handle(DiscordApiService $discord): int
    {
        if (! $discord->enabled()) {
            $this->warn('Discord bot niet geconfigureerd (DISCORD_BOT_TOKEN / DISCORD_GUILD_ID). Overgeslagen.');

            return self::SUCCESS;
        }

        // Index existing events by name so we can dedupe / update.
        $existing = [];
        foreach ($discord->scheduledEvents() as $event) {
            $existing[$event['name'] ?? ''] = $event;
        }

        $created = 0;
        $updated = 0;

        foreach (Schedule::with(['games', 'blocks'])->get() as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                if (! $item->start || ! $item->end || $item->start->isPast()) {
                    continue;
                }

                $name = $item->is_tournament ? "Toernooi: {$item->name}" : $item->name;

                if (isset($existing[$name])) {
                    $current = $existing[$name];
                    $startChanged = ($current['scheduled_start_time'] ?? null) !== $item->start->toAtomString();
                    if ($startChanged && ! empty($current['id'])) {
                        if ($discord->modifyScheduledEvent($current['id'], $item->start, $item->end)) {
                            $updated++;
                        }
                    }

                    continue;
                }

                if ($discord->createScheduledEvent($name, $item->start, $item->end, $item->schedule_name)) {
                    $created++;
                }
            }
        }

        $this->info("Events aangemaakt: {$created}, bijgewerkt: {$updated}.");

        return self::SUCCESS;
    }
}
