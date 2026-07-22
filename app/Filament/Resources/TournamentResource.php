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
use Filament\Schemas\Components\Section;
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
        $presets = Tournament::scoringPresets();

        return $form
            ->schema([
                Section::make('Toernooi')
                    ->description('Basisgegevens en koppeling aan een game en speeldag.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Naam')
                            ->required()
                            ->maxLength(255),

                        Select::make('game_id')
                            ->label('Game')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('schedule_id')
                            ->label('Speeldag')
                            ->relationship('schedule', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('description')
                            ->label('Omschrijving')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('rules')
                            ->label('Spelregels')
                            ->helperText('Optioneel. Wordt aan spelers getoond op de toernooipagina.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Scoresysteem')
                    ->description('Bepaal hoe er gescoord wordt. Elk puntensysteem is mogelijk.')
                    ->columns(2)
                    ->schema([
                        Select::make('scoring_type')
                            ->label('Type scoring')
                            ->options(collect($presets)->map(fn ($p) => $p['label'])->toArray())
                            ->default('points')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) use ($presets) {
                                if (isset($presets[$state])) {
                                    $set('score_label', $presets[$state]['score_label']);
                                    $set('higher_is_better', $presets[$state]['higher_is_better']);
                                }
                            })
                            ->helperText('Kies een preset. Punten/kills/goals: hoogste wint. Tijd/strafpunten: laagste wint.'),

                        TextInput::make('score_label')
                            ->label('Naam van de eenheid')
                            ->default('Punten')
                            ->required()
                            ->helperText('Bijv. Punten, Seconden, Kills, Goals.'),

                        Toggle::make('higher_is_better')
                            ->label('Hoogste score wint')
                            ->default(true)
                            ->helperText('Zet uit voor tijd-gebaseerde toernooien waar de laagste score wint.'),

                        Toggle::make('is_team_based')
                            ->label('Team-gebaseerd')
                            ->onIcon('heroicon-o-user-group')
                            ->offIcon('heroicon-o-user')
                            ->default(false)
                            ->helperText('Spelers strijden in teams in plaats van individueel.'),
                    ]),

                Section::make('Planning en status')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('time_start')
                            ->seconds(false)
                            ->label('Starttijd')
                            ->required(),

                        TimePicker::make('time_end')
                            ->seconds(false)
                            ->label('Eindtijd')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Actief (live)')
                            ->onIcon('heroicon-o-check')
                            ->default(false)
                            ->helperText('Toont dit toernooi als live op de ladder.'),

                        Toggle::make('concluded')
                            ->label('Afgerond')
                            ->default(false),
                    ]),
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
