<?php

namespace App\Filament\Resources\SignupResource\Pages;

use App\Filament\Resources\SignupResource;
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
}
