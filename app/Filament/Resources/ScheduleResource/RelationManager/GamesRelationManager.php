<?php

namespace App\Filament\Resources\ScheduleResource\RelationManager;

use App\Models\Game;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Carbon\Carbon;

class GamesRelationManager extends RelationManager
{
    protected static string $relationship = 'games';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('game_id')
                    ->label('Game')
                    ->options(fn() => Game::orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->placeholder('Select a game'),
                TimePicker::make('start_time')
                    ->label('Start Time')
                    ->native(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('H:i')
                    ->seconds(false)
                    ->hoursStep(1)
                    ->minutesStep(15),
                TimePicker::make('end_time')
                    ->label('End Time')
                    ->native(false)
                    ->required()
                    ->format('H:i')
                    ->displayFormat('H:i')
                    ->seconds(false)
                    ->hoursStep(1)
                    ->minutesStep(15),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Game')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pivot.start_date')
                    ->label('Start Time')
                    ->dateTime('H:i')
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('game_schedule.start_date', $direction);
                    }),
                TextColumn::make('pivot.end_date')
                    ->label('End Time')
                    ->dateTime('H:i')
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('game_schedule.end_date', $direction);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->form(fn() => [
                        Select::make('recordId')
                            ->label('Game')
                            ->options(fn() => \App\Models\Game::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a game'),
                        TimePicker::make('start_time')
                            ->label('Start Time')
                            ->native(false)
                            ->required()
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->seconds(false)
                            ->hoursStep(1)
                            ->minutesStep(15),
                        TimePicker::make('end_time')
                            ->label('End Time')
                            ->native(false)
                            ->required()
                            ->format('H:i')
                            ->displayFormat('H:i')
                            ->seconds(false)
                            ->hoursStep(1)
                            ->minutesStep(15),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $schedule = $this->getOwnerRecord();
                        $startDate = Carbon::parse($schedule->date)->setTimeFromTimeString($data['start_time']);
                        $endDate = Carbon::parse($schedule->date)->setTimeFromTimeString($data['end_time']);

                        if ($endDate->lessThanOrEqualTo($startDate)) {
                            $endDate->addDay();
                        }

                        return [
                            'recordId' => $data['recordId'],
                            'start_date' => $startDate->format('Y-m-d H:i:s'),
                            'end_date' => $endDate->format('Y-m-d H:i:s'),
                        ];
                    })
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
