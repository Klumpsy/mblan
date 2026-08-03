<?php

namespace App\Filament\Resources\EditionResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Wie was erbij dit jaar. Bevestigde aanmeldingen komen hier automatisch
 * binnen; voor oudere edities voeg je deelnemers handmatig toe.
 */
class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Deelnemers';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->description('Bevestigde aanmeldingen komen hier automatisch binnen; voeg voor oudere edities zelf deelnemers toe.')
            ->emptyStateHeading('Nog geen deelnemers')
            ->emptyStateDescription('Voeg deelnemers toe met "Deelnemer toevoegen".')
            ->columns([
                TextColumn::make('name')
                    ->label('Speler')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Deelnemers toevoegen')
                    ->multiple()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelect(fn (Select $select) => $select->placeholder('Kies een of meer spelers'))
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make()
                    ->label('Verwijderen'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
