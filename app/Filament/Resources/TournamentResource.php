<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TournamentResource\Pages;
use App\Filament\Resources\TournamentResource\RelationManager\RegistrationsRelationManager;
use App\Filament\Resources\TournamentResource\RelationManager\RoundsRelationManager;
use App\Filament\Resources\TournamentResource\RelationManager\UsersRelationManager;
use App\Models\Tournament;
use Filament\Forms\Components\Placeholder;
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
use Illuminate\Support\HtmlString;

class TournamentResource extends Resource
{
    protected static ?string $model = Tournament::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bolt';

    protected static string | \UnitEnum | null $navigationGroup = 'Deze editie';

    public static function form(Schema $form): Schema
    {
        $presets = Tournament::scoringPresets();

        return $form
            ->schema([
                Section::make('Zo houd je scores bij')
                    ->description('Korte handleiding')
                    ->icon('heroicon-o-information-circle')
                    ->collapsible()
                    ->schema([
                        Placeholder::make('handleiding')
                            ->hiddenLabel()
                            ->content(new HtmlString(<<<'HTML'
                                <ol class="list-decimal space-y-2 ps-5 text-sm leading-relaxed">
                                    <li><strong>Stap 1 · Aanmeldingen.</strong> Spelers melden zich aan via de site; je ziet (en beheert) ze op het tabblad <strong>Aanmeldingen</strong>. Iedereen die is aangemeld staat automatisch op het scorebord en doet mee met de rondes.</li>
                                    <li>Kies onder <strong>Scoresysteem</strong> hoe er gescoord wordt: punten/kills (hoogste wint) of <strong>tijd</strong> (laagste wint). Voor tijd voer je scores in als <strong>minuten, seconden en milliseconden</strong>.</li>
                                    <li><strong>Stap 2 · Teams of lobby's.</strong> Op het tabblad <strong>Scores</strong> verdeel je het veld met één klik: kies <strong>aantal teams</strong> of <strong>spelers per team</strong>. Bij individuele toernooien (zoals Hearthstone) heten dit lobby's en tellen de scores gewoon per speler. Shuffelen kan altijd opnieuw, en met <strong>Teams aankondigen</strong> post je de indeling in Discord.</li>
                                    <li><strong>Stap 3 · Rondes.</strong> Op het tabblad <strong>Rondes</strong> klik je na elke ronde op <strong>Ronde toevoegen</strong> en vul je de punten per speler (of per team) in. Totalen en ranking rekenen zichzelf uit; een ronde achteraf bewerken of verwijderen corrigeert alles automatisch.</li>
                                    <li>Losse correcties kunnen ook: <strong>Score toevoegen</strong> telt punten bij het totaal op, <strong>Score bijwerken</strong> zet de exacte stand. Let op: bij een toernooi mét rondes zijn de rondepunten leidend.</li>
                                    <li>Zet onderaan <strong>Afgerond</strong> aan als het klaar is. De eindstand verschijnt dan op de site en in Discord.</li>
                                </ol>
                            HTML)),
                    ]),
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

                        Select::make('edition_id')
                            ->label('Editie')
                            ->relationship('edition', 'name')
                            ->default(fn () => \App\Models\Edition::current()?->id)
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
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Aanmeldingen')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('game.name')->label('Game'),
                TextColumn::make('schedule.name')->sortable(),
                TextColumn::make('time_start')->dateTime(),
                TextColumn::make('time_end')->dateTime(),
                TextColumn::make('edition.name')
                    ->label('Editie')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('edition_id')
                    ->label('Editie')
                    ->relationship('edition', 'name')
                    // Standaard zie je de actieve editie; kies een jaar voor het archief.
                    ->default(\App\Models\Edition::current()?->id),
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
            RegistrationsRelationManager::class,
            UsersRelationManager::class,
            RoundsRelationManager::class,
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
