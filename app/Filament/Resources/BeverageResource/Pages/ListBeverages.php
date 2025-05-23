<?php

namespace App\Filament\Resources\BeverageResource\Pages;

use App\Filament\Resources\BeverageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBeverages extends ListRecords
{
    protected static string $resource = BeverageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
