<?php

namespace App\Filament\Widgets;

use App\Models\DiscordCommandLog;
use Filament\Widgets\ChartWidget;

/**
 * How often each Discord slash command is used, from DiscordCommandLog.
 */
class DiscordCommandUsageWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Discord commando-gebruik';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $counts = DiscordCommandLog::query()
            ->selectRaw('command, count(*) as total')
            ->groupBy('command')
            ->orderByDesc('total')
            ->pluck('total', 'command');

        return [
            'datasets' => [[
                'label' => 'Aantal keer gebruikt',
                'data' => $counts->values()->all(),
                'backgroundColor' => '#37c26f',
            ]],
            'labels' => $counts->keys()->map(fn (string $command): string => '/'.$command)->all(),
        ];
    }
}
