<?php

namespace App\Filament\Resources\PizzaRoundResource\Pages;

use App\Filament\Resources\PizzaRoundResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPizzaRounds extends ListRecords
{
    protected static string $resource = PizzaRoundResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
