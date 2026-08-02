<?php

namespace App\Filament\Resources\TournamentResource\Pages;

use App\Filament\Resources\TournamentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTournament extends EditRecord
{
    protected static string $resource = TournamentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Some games score "golf-style": the lowest score wins. One tap
            // flips the whole ladder (and back), rankings included.
            Actions\Action::make('invertRanking')
                ->label('Volgorde omdraaien')
                ->icon('heroicon-o-arrows-up-down')
                ->action(function () {
                    $tournament = $this->getRecord();
                    $tournament->update(['higher_is_better' => ! $tournament->higher_is_better]);
                    $tournament->recalculateRankings();

                    Notification::make()
                        ->title($tournament->higher_is_better
                            ? 'Hoogste score wint nu'
                            : 'Laagste score wint nu')
                        ->body('De ranglijst is omgedraaid.')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
