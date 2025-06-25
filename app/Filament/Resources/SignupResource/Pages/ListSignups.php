<?php

namespace App\Filament\Resources\SignupResource\Pages;

use App\Filament\Resources\SignupResource;
use App\Filament\Resources\SignupResource\Widgets\CostsOverview;
use App\Filament\Resources\SignupResource\Widgets\CostsPerSignupTable;
use App\Filament\Resources\SignupResource\Widgets\SignupFeatureChart;
use App\Filament\Resources\SignupResource\Widgets\TshirtSizeChart;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSignups extends ListRecords
{
    protected static string $resource = SignupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            CostsOverview::class,
            TshirtSizeChart::class,
            SignupFeatureChart::class,
        ];
    }
}
