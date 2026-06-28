<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bolt';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-o-check')
                    ->default(false),

                Toggle::make('is_team_based')
                    ->label('Team Based Tournament')
                    ->onIcon('heroicon-o-user-group')
                    ->offIcon('heroicon-o-user')
                    ->default(false)
                    ->helperText('Enable this for team-based tournaments where players compete in teams.'),

                Textarea::make('description')
                    ->rows(3),

                TimePicker::make('time_start')
                    ->seconds(false)
                    ->label('Start Time')
                    ->required(),

                TimePicker::make('time_end')
                    ->seconds(false)
                    ->label('End Time')
                    ->required(),

                Select::make('game_id')
                    ->relationship('game', 'name')
                    ->required(),

                Select::make('schedule_id')
                    ->relationship('schedule', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->sortable()->searchable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                ToggleColumn::make('is_team_based')
                    ->label('Team Based')
                    ->onIcon('heroicon-o-user-group')
                    ->offIcon('heroicon-o-user'),
                ToggleColumn::make('concluded')
                    ->label('Concluded'),
                TextColumn::make('game.name')->label('Game'),
                TextColumn::make('schedule.name')->sortable(),
                TextColumn::make('schedule.edition.year')->sortable(),
                TextColumn::make('time_start')->dateTime(),
                TextColumn::make('time_end')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTournaments::route('/'),
            'create' => Pages\CreateTournament::route('/create'),
            'edit' => Pages\EditTournament::route('/{record}/edit'),
        ];
    }
}
