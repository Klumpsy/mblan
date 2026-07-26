<?php

namespace App\Filament\Resources\PizzaRoundResource\RelationManagers;

use App\Models\User;
use App\Support\PizzaMenu;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * The combined order list for one round: user -> pizza + notes. This is the
 * single sheet admins read off when calling the order through.
 */
class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Bestellingen';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('user_id')
                ->label('Deelnemer')
                ->options(fn () => User::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Select::make('pizza')
                ->label('Menukeuze')
                ->options(PizzaMenu::grouped())
                ->searchable()
                ->required(),
            Textarea::make('notes')->label('Opmerkingen')->rows(2)->maxLength(500),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Deelnemer')->searchable()->sortable(),
                TextColumn::make('pizza')->label('Keuze')->searchable()->wrap(),
                TextColumn::make('notes')->label('Opmerkingen')->wrap()->placeholder('-'),
            ])
            ->defaultSort('user.name')
            ->headerActions([
                CreateAction::make()->label('Bestelling toevoegen'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
