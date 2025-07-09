<?php

use App\Models\Game;
use Filament\Tables\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;

class GamesRelationManager extends RelationManager
{
    protected static string $relationship = 'games';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('game_id')
                    ->label('Game')
                    ->options(Game::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->disabled(fn($context) => $context === 'edit'),
                Toggle::make('is_tournament')
                    ->label('Is Tournament')
                    ->default(false),
                DateTimePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->native(false),
                DateTimePicker::make('end_date')
                    ->label('End Date')
                    ->required()
                    ->native(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Game Name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('pivot.is_tournament')
                    ->label('Tournament')
                    ->boolean()
                    ->trueIcon('heroicon-o-trophy')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),
                TextColumn::make('pivot.start_date')
                    ->label('Start Date')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
                TextColumn::make('pivot.end_date')
                    ->label('End Date')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tournament_only')
                    ->label('Tournament Games Only')
                    ->query(fn($query) => $query->wherePivot('is_tournament', true)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): mixed {
                        $game = Game::find($data['game_id']);
                        $this->ownerRecord->games()->attach($game, [
                            'is_tournament' => $data['is_tournament'],
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                        ]);
                        return $game;
                    }),
            ])
            ->actions([
                Action::make('toggleTournament')
                    ->label('')
                    ->icon(fn($record) => $record->pivot->is_tournament ? 'heroicon-o-trophy' : 'heroicon-o-plus-circle')
                    ->color(fn($record) => $record->pivot->is_tournament ? 'success' : 'primary')
                    ->tooltip(fn($record) => $record->pivot->is_tournament ? 'Remove from tournament' : 'Add to tournament')
                    ->action(function ($record) {
                        $pivotData = $record->pivot;
                        $record->schedules()->updateExistingPivot(
                            $this->ownerRecord->id,
                            ['is_tournament' => !$pivotData->is_tournament]
                        );
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn($record) => $record->pivot->is_tournament ? 'Remove from Tournament' : 'Add to Tournament')
                    ->modalDescription(fn($record) => $record->pivot->is_tournament
                        ? 'Are you sure you want to remove this game from the tournament?'
                        : 'Are you sure you want to add this game to the tournament?')
                    ->modalSubmitActionLabel(fn($record) => $record->pivot->is_tournament ? 'Remove' : 'Add'),
                EditAction::make()
                    ->using(function (array $data, $record): mixed {
                        $this->ownerRecord->games()->updateExistingPivot($record->id, [
                            'is_tournament' => $data['is_tournament'],
                            'start_date' => $data['start_date'],
                            'end_date' => $data['end_date'],
                        ]);
                        return $record;
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggleTournament')
                        ->label('Toggle Tournament Status')
                        ->icon('heroicon-o-trophy')
                        ->color('primary')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $pivotData = $record->pivot;
                                $record->schedules()->updateExistingPivot(
                                    $this->ownerRecord->id,
                                    ['is_tournament' => !$pivotData->is_tournament]
                                );
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Toggle Tournament Status')
                        ->modalDescription('Are you sure you want to toggle the tournament status for the selected games?')
                        ->modalSubmitActionLabel('Toggle'),
                ]),
            ]);
    }
}
