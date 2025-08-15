<?php

namespace App\Filament\Resources\SignupResource\Pages;

use App\Filament\Resources\SignupResource;
use App\Filament\Resources\SignupResource\Widgets\CostsOverview;
use App\Filament\Resources\SignupResource\Widgets\SignupFeatureChart;
use App\Filament\Resources\SignupResource\Widgets\TshirtSizeChart;
use App\Filament\Resources\SignupResource\Widgets\BeerLeaderboardWidget;
use App\Filament\Resources\SignupResource\Widgets\PizzaOrdersWidget;
use App\Filament\Resources\SignupResource\Widgets\RealtimeBeerActivityWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSignups extends ListRecords
{
    protected static string $resource = SignupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CostsOverview::class,
            TshirtSizeChart::class,
            SignupFeatureChart::class,
            RealtimeBeerActivityWidget::class,
            BeerLeaderboardWidget::class,
            PizzaOrdersWidget::class,
        ];
    }
}
