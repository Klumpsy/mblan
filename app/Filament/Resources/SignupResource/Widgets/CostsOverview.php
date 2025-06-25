<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use App\Models\Signup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CostsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $signups = Signup::all();
        $signupCount = $signups->count();

        if ($signupCount === 0) {
            return [
                Stat::make('Total Signups', 0)
                    ->description('No signups yet')
                    ->color('gray'),
            ];
        }

        $totalCost = $signups->reduce(function ($carry, Signup $signup) use ($signupCount) {
            return $carry + $signup->calculateCost($signupCount);
        }, 0);

        $averageCost = round($totalCost / $signupCount, 2);

        return [
            Stat::make('Total Signups', $signupCount)
                ->description('Total number of signups')
                ->color('gray'),

            Stat::make('Average Cost Per User', '€' . number_format($averageCost, 2))
                ->description('Includes shared toilet/douche and daily costs')
                ->color('info'),

            Stat::make('Total Cost', '€' . number_format($totalCost, 2))
                ->description('All user costs including shared expenses')
                ->color('success'),
        ];
    }
}
