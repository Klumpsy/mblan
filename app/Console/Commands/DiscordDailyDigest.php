<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Services\DiscordWebhookService;
use App\Support\ScheduleTimeline;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Posts the programme for a given day to Discord. Defaults to today; pass a date
 * (Y-m-d) to preview another day. Meant to run once each morning of the event.
 */
class DiscordDailyDigest extends Command
{
    protected $signature = 'discord:daily-digest {date? : Y-m-d, defaults to today}';
    protected $description = 'Post the day programme to Discord';

    public function handle(DiscordWebhookService $discord): int
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now();

        $schedules = Schedule::with(['games', 'blocks'])
            ->whereDate('date', $date->toDateString())
            ->get();

        $lines = [];
        foreach ($schedules as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                $time = $item->start ? $item->start->format('H:i') : 't.b.a.';
                $tag = $item->is_tournament ? ' (toernooi)' : '';
                $lines[] = "{$time} - {$item->name}{$tag}";
            }
        }

        $ok = $discord->sendDailyDigest($date, $lines);
        $this->info($ok ? 'Dagprogramma verstuurd.' : 'Niets verstuurd (geen webhook of geen items).');

        return self::SUCCESS;
    }
}
