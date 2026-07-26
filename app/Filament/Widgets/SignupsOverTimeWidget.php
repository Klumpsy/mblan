<?php

namespace App\Filament\Widgets;

use App\Models\Signup;
use Filament\Widgets\ChartWidget;

/**
 * LAN sign-ups per day over the last 30 days — shows momentum in the run-up.
 */
class SignupsOverTimeWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Aanmeldingen over tijd';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = 30;
        $start = now()->subDays($days - 1)->startOfDay();

        $perDay = Signup::where('created_at', '>=', $start)
            ->get()
            ->groupBy(fn (Signup $signup): string => $signup->created_at->format('Y-m-d'))
            ->map->count();

        $labels = [];
        $data = [];
        $cursor = $start->copy();
        for ($i = 0; $i < $days; $i++) {
            $labels[] = $cursor->format('d-m');
            $data[] = $perDay->get($cursor->format('Y-m-d'), 0);
            $cursor->addDay();
        }

        return [
            'datasets' => [[
                'label' => 'Aanmeldingen per dag',
                'data' => $data,
                'borderColor' => '#37c26f',
                'backgroundColor' => 'rgba(55, 194, 111, 0.15)',
                'fill' => true,
                'tension' => 0.3,
            ]],
            'labels' => $labels,
        ];
    }
}
