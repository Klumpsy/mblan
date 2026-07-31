<?php

namespace App\Filament\Resources\TournamentResource\RelationManager;

use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Shows everyone who signed up for a tournament, so admins can see the field
 * up front and manually add or remove players when needed.
 */
class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Aanmeldingen';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->description('Stap 1 · Wie doet er mee? Aanmeldingen via de site komen hier automatisch binnen; je kunt ook zelf spelers toevoegen.')
            ->emptyStateHeading('Nog geen aanmeldingen')
            ->emptyStateDescription('Spelers melden zich aan via de site, of voeg ze hier zelf toe met "Speler toevoegen".')
            ->columns([
                TextColumn::make('name')
                    ->label('Speler')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('pivot.created_at')
                    ->label('Aangemeld op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('tournament_registrations.created_at', $direction)),
            ])
            ->defaultSort('tournament_registrations.created_at', 'asc')
            ->headerActions([
                AttachAction::make()
                    ->label('Speler toevoegen')
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelect(fn (Select $select) => $select->placeholder('Kies een speler'))
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
