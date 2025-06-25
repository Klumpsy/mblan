<?php

namespace App\Filament\Resources\SignupResource\Widgets;

use App\Models\Signup;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TshirtSizeChart extends BaseWidget
{
    protected function getStats(): array
    {
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];

        $data = Signup::where('wants_tshirt', true)
            ->whereNotNull('tshirt_size')
            ->selectRaw('tshirt_size, COUNT(*) as count')
            ->groupBy('tshirt_size')
            ->pluck('count', 'tshirt_size');

        return collect($sizes)->map(function ($size) use ($data) {
            return Stat::make("Size $size", $data[$size] ?? 0)
                ->description("T-Shirts in size $size")

                ->color('info');
        })->toArray();
    }
}
