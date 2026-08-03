<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PizzaRoundResource\Pages;
use App\Filament\Resources\PizzaRoundResource\RelationManagers\OrdersRelationManager;
use App\Models\PizzaRound;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PizzaRoundResource extends Resource
{
    protected static ?string $model = PizzaRound::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cake';

    protected static string | \UnitEnum | null $navigationGroup = 'Deze editie';

    protected static ?string $navigationLabel = 'Pizza-bestellingen';

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')
                ->label('Naam van de ronde')
                ->required()
                ->maxLength(255)
                ->helperText('Bijv. "Vrijdagavond" of "Zaterdag lunch".'),

            Toggle::make('is_open')
                ->label('Open voor bestellingen')
                ->default(true)
                ->helperText('Zolang dit aan staat kunnen deelnemers bestellen of hun keuze wijzigen.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_open')->label('Open')->boolean(),
                TextColumn::make('orders_count')->label('Bestellingen')->counts('orders'),
                TextColumn::make('created_at')->label('Aangemaakt')->dateTime('d-m-Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPizzaRounds::route('/'),
            'create' => Pages\CreatePizzaRound::route('/create'),
            'edit' => Pages\EditPizzaRound::route('/{record}/edit'),
        ];
    }
}
