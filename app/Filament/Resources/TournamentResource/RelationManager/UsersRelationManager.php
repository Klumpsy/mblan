<?php

namespace App\Filament\Resources\TournamentResource\RelationManager;

use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'usersWithScores';

    protected function recalculateRanking(): void
    {
        $tournament = $this->getOwnerRecord();

        $users = $tournament->usersWithScores()
            ->orderByPivot('score', 'desc')
            ->get();

        foreach ($users as $index => $user) {
            $rank = $index + 1;
            $tournament->usersWithScores()->updateExistingPivot($user->id, [
                'ranking' => $rank,
            ]);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('User'),
                TextColumn::make('pivot.score')->label('Score')->sortable(query: fn($query, $direction) => $query->orderBy('tournament_user_pivot.score', $direction)),
                TextColumn::make('pivot.ranking')->label('Ranking')->sortable(query: fn($query, $direction) => $query->orderBy('tournament_user_pivot.ranking', $direction)),
            ])
            ->defaultSort('tournament_user_pivot.ranking', 'asc')
            ->headerActions([
                AttachAction::make()
                    ->form(fn() => [
                        Select::make('recordId')
                            ->label('User')
                            ->options(function () {
                                $tournament = $this->getOwnerRecord();
                                $attachedUserIds = $tournament->usersWithScores()->pluck('users.id')->toArray();

                                return User::whereNotIn('id', $attachedUserIds)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required()
                            ->preload()
                            ->placeholder('Select a user'),
                        TextInput::make('score')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->mutateFormDataUsing(fn(array $data) => [
                        'recordId' => $data['recordId'],
                        'score' => $data['score'],
                    ])
                    ->after(fn() => $this->recalculateRanking())
                    ->preloadRecordSelect(),
            ])
            ->actions([
                EditAction::make()
                    ->after(fn() => $this->recalculateRanking()),
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('score')->numeric()->required()
                ->label('Score')
                ->helperText('Enter the score for the user in this tournament.'),
        ]);
    }
}
