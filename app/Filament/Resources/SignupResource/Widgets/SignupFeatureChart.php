<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use App\Models\Signup;
use Filament\Widgets\ChartWidget;

class SignupFeatureChart extends ChartWidget
{
    protected static ?string $heading = 'Signup Feature Breakdown';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Signup::where('joins_barbecue', true)->count(),
                        Signup::where('is_vegan', true)->count(),
                        Signup::where('wants_tshirt', true)->count(),
                        Signup::where('stays_on_campsite', true)->count(),
                        Signup::where('has_paid', true)->count(),
                        Signup::where('joins_pizza', true)->count(),
                    ],
                    'backgroundColor' => ['#f87171', '#34d399', '#60a5fa', '#facc15', '#a78bfa', '#fbbf24'],
                ],
            ],
            'labels' => [
                'Joins BBQ 🍖',
                'Is Vegan 🌱',
                'Wants T-Shirt 👕',
                'Campsite 🏕️',
                'Has Paid 💰',
                'Joins Pizza 🍕',
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): ?array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getHeight(): string|int|null
    {
        return 300;
    }
}
