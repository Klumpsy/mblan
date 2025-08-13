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
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'usersWithScores';

    protected function recalculateRanking(): void
    {
        $tournament = $this->getOwnerRecord();

        if ($tournament->is_team_based) {
            $teamScores = DB::table('tournament_user')
                ->select('team_number', 'team_name', DB::raw('MAX(team_score) as total_score'))
                ->where('tournament_id', $tournament->id)
                ->whereNotNull('team_number')
                ->groupBy('team_number', 'team_name')
                ->orderBy('total_score', 'desc')
                ->orderBy('team_number', 'asc')
                ->get();

            foreach ($teamScores as $index => $team) {
                $rank = $index + 1;
                DB::table('tournament_user')
                    ->where('tournament_id', $tournament->id)
                    ->where('team_number', $team->team_number)
                    ->update(['ranking' => $rank]);
            }
        } else {
            $users = $tournament->usersWithScores()
                ->orderByPivot('score', 'desc')
                ->get();

            foreach ($users as $index => $user) {
                $tournament->usersWithScores()->updateExistingPivot($user->id, [
                    'ranking' => $index + 1,
                ]);
            }
        }
    }

    /**
     * Create teams (kept exactly like you had it, including shuffle + assignment).
     */
    protected function createTeams(int $teamSize): void
    {
        $tournament = $this->getOwnerRecord();

        // Get users without teams
        $usersWithoutTeams = $tournament->usersWithScores()
            ->whereNull('team_number')
            ->get()
            ->shuffle(); // Shuffle for random teams

        if ($usersWithoutTeams->count() < 1) {
            Notification::make()
                ->title('No users available')
                ->body("No users without teams available.")
                ->danger()
                ->send();
            return;
        }

        // Get the next team number using direct DB query
        $lastTeamNumber = DB::table('tournament_user')
            ->where('tournament_id', $tournament->id)
            ->whereNotNull('team_number')
            ->max('team_number') ?? 0;

        $teamNumber = $lastTeamNumber + 1;
        $teamsCreated = 0;
        $usersProcessed = 0;

        // Create teams of the specified size
        while ($usersProcessed < $usersWithoutTeams->count()) {
            $remainingUsers = $usersWithoutTeams->count() - $usersProcessed;

            // If we have enough users for a full team, create it
            if ($remainingUsers >= $teamSize) {
                $teamMembers = $usersWithoutTeams->slice($usersProcessed, $teamSize);
                $actualTeamSize = $teamSize;
            } else {
                // Create individual teams for remaining users
                $teamMembers = $usersWithoutTeams->slice($usersProcessed, 1);
                $actualTeamSize = 1;
            }

            $teamName = $actualTeamSize > 1 ? "Team {$teamNumber}" : "Solo {$teamNumber}";

            foreach ($teamMembers as $user) {
                DB::table('tournament_user')
                    ->where('tournament_id', $tournament->id)
                    ->where('user_id', $user->id)
                    ->update([
                        'team_name' => $teamName,
                        'team_number' => $teamNumber,
                    ]);
            }

            $usersProcessed += $actualTeamSize;
            $teamNumber++;
            $teamsCreated++;
        }

        $this->recalculateRanking();

        Notification::make()
            ->title('Teams created successfully')
            ->body("Created {$teamsCreated} teams. All users have been assigned.")
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        $tournament = $this->getOwnerRecord();

        $columns = [
            TextColumn::make('name')
                ->label('User')
                ->searchable(),
        ];

        if ($tournament->is_team_based) {
            $columns[] = TextColumn::make('pivot.team_name')
                ->label('Team')
                ->sortable()
                ->placeholder('No Team');
            $columns[] = TextColumn::make('pivot.team_number')
                ->label('Team #')
                ->sortable()
                ->placeholder('-');
            $columns[] = TextColumn::make('pivot.team_score')
                ->label('Team Score')
                ->sortable(
                    query: fn($query, $direction) =>
                    $query->orderBy('tournament_user.team_score', $direction)
                )
                ->placeholder('-');
        } else {
            $columns[] = TextColumn::make('pivot.score')
                ->label('Score')
                ->sortable(
                    query: fn($query, $direction) =>
                    $query->orderBy('tournament_user.score', $direction)
                )
                ->placeholder('-');
        }

        $columns[] = TextColumn::make('pivot.ranking')
            ->label('Ranking')
            ->sortable(query: fn($query, $direction) => $query->orderBy('tournament_user.ranking', $direction))
            ->badge()
            ->color(fn($state) => match (true) {
                $state <= 3 => 'success',
                $state <= 10 => 'warning',
                default => 'gray',
            });

        // --- Header actions (includes your Create Teams + Shuffle Teams buttons as-is) ---
        $headerActions = [
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
        ];

        if ($tournament->is_team_based) {
            $headerActions[] = Action::make('create_teams')
                ->label('Create Teams')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->form([
                    TextInput::make('team_size')
                        ->label('Team Size')
                        ->numeric()
                        ->required()
                        ->minValue(2)
                        ->maxValue(10)
                        ->default(2)
                        ->helperText('Number of players per team'),
                ])
                ->action(function (array $data): void {
                    $this->createTeams($data['team_size']);
                })
                ->requiresConfirmation()
                ->modalDescription('This will automatically create teams from users who are not currently on a team.');

            $headerActions[] = Action::make('shuffle_teams')
                ->label('Shuffle Teams')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    TextInput::make('team_size')
                        ->label('Team Size')
                        ->numeric()
                        ->required()
                        ->minValue(2)
                        ->maxValue(10)
                        ->default(2)
                        ->helperText('Number of players per team'),
                ])
                ->action(function (array $data): void {
                    $tournament = $this->getOwnerRecord();

                    // Clear all team assignments
                    DB::table('tournament_user')
                        ->where('tournament_id', $tournament->id)
                        ->update([
                            'team_name' => null,
                            'team_number' => null,
                        ]);

                    // Shuffle all users into new teams
                    $allUsers = $tournament->usersWithScores()->get()->shuffle();
                    $teamSize = $data['team_size'];
                    $teamNumber = 1;
                    $teamsCreated = 0;
                    $usersProcessed = 0;

                    while ($usersProcessed < $allUsers->count()) {
                        $remainingUsers = $allUsers->count() - $usersProcessed;
                        if ($remainingUsers >= $teamSize) {
                            $teamMembers = $allUsers->slice($usersProcessed, $teamSize);
                            $actualTeamSize = $teamSize;
                        } else {
                            $teamMembers = $allUsers->slice($usersProcessed, 1);
                            $actualTeamSize = 1;
                        }
                        $teamName = $actualTeamSize > 1 ? "Team {$teamNumber}" : "Solo {$teamNumber}";

                        foreach ($teamMembers as $user) {
                            DB::table('tournament_user')
                                ->where('tournament_id', $tournament->id)
                                ->where('user_id', $user->id)
                                ->update([
                                    'team_name' => $teamName,
                                    'team_number' => $teamNumber,
                                ]);
                        }

                        $usersProcessed += $actualTeamSize;
                        $teamNumber++;
                        $teamsCreated++;
                    }

                    $this->recalculateRanking();

                    Notification::make()
                        ->title('Teams shuffled successfully')
                        ->body("Shuffled into {$teamsCreated} teams. All users have been assigned.")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->modalDescription('This will clear all existing teams and create new random teams.');
        }

        // --- Row actions ---
        $actions = [
            EditAction::make()
                ->form(fn(Form $form) => $this->form($form))
                ->mutateFormDataUsing(function (array $data, $record) {
                    $tournament = $this->getOwnerRecord();

                    // Current score from pivot
                    $currentScore = $record->pivot->score ?? 0;

                    // Determine new score
                    if (isset($data['add_to_score']) && $data['add_to_score'] !== null) {
                        $newScore = $currentScore + $data['add_to_score'];
                    } else {
                        $newScore = $data['score'];
                    }

                    // Update this user
                    $tournament->usersWithScores()->updateExistingPivot(
                        $record->id,
                        array_filter([
                            'score' => $newScore,
                            'team_name' => $data['team_name'] ?? null,
                            'team_score' => $tournament->is_team_based ? $newScore : null,
                        ], fn($v) => true)
                    );

                    // If team-based, also update teammates' scores
                    if ($tournament->is_team_based && !is_null($record->pivot->team_number)) {
                        DB::table('tournament_user')
                            ->where('tournament_id', $tournament->id)
                            ->where('team_number', $record->pivot->team_number)
                            ->update([
                                'score' => $newScore,
                                'team_score' => $newScore,
                            ]);
                    }

                    return $data;
                })
                ->after(fn() => $this->recalculateRanking()),

            DetachAction::make(),
        ];

        if ($tournament->is_team_based) {
            $actions[] = Action::make('remove_from_team')
                ->label('Remove from Team')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->action(function ($record): void {
                    $tournament = $this->getOwnerRecord();
                    $tournament->usersWithScores()->updateExistingPivot($record->id, [
                        'team_name' => null,
                        'team_number' => null,
                        // Optional: clear team_score/score on removal if you prefer
                        // 'team_score' => null,
                        // 'score' => null,
                    ]);

                    $this->recalculateRanking();

                    Notification::make()
                        ->title('User removed from team')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn($record) => !is_null($record->pivot->team_number));
        }

        $bulkActions = [DetachBulkAction::make()];

        if ($tournament->is_team_based) {
            $bulkActions[] = BulkAction::make('create_team_from_selection')
                ->label('Create Team from Selection')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->form([
                    TextInput::make('team_name')
                        ->label('Team Name')
                        ->required()
                        ->placeholder('Enter team name'),
                ])
                ->action(function (Collection $records, array $data): void {
                    $tournament = $this->getOwnerRecord();

                    // Get the next team number
                    $lastTeamNumber = $tournament->usersWithScores()
                        ->whereNotNull('team_number')
                        ->max('team_number') ?? 0;
                    $teamNumber = $lastTeamNumber + 1;

                    foreach ($records as $record) {
                        $tournament->usersWithScores()->updateExistingPivot($record->id, [
                            'team_name' => $data['team_name'],
                            'team_number' => $teamNumber,
                        ]);
                    }

                    $this->recalculateRanking();

                    Notification::make()
                        ->title('Team created successfully')
                        ->body("Created team '{$data['team_name']}' with " . $records->count() . " members.")
                        ->success()
                        ->send();
                })
                ->deselectRecordsAfterCompletion();
        }

        // --- Build the table ---
        $table = $table
            ->columns($columns)
            ->defaultSort('tournament_user.ranking', 'asc')
            ->headerActions($headerActions)
            ->actions($actions)
            ->bulkActions($bulkActions);

        // --- Team mode: visually group members by team with a header showing team name & score ---
        if ($tournament->is_team_based) {
            $table = $table->groups([
                Group::make('pivot.team_number')
                    ->label('Team')
                    ->collapsible()
                    ->getTitleFromRecordUsing(function ($record): string {
                        $teamName = $record->pivot->team_name ?? 'No Team';
                        $teamNo   = $record->pivot->team_number ?? '-';
                        $score    = $record->pivot->team_score ?? '-';
                        return "{$teamName} (#{ $teamNo }) — Score: {$score}";
                    }),
            ]);
        }

        return $table;
    }

    public function form(Form $form): Form
    {
        $tournament = $this->getOwnerRecord();

        $schema = [
            TextInput::make('score')
                ->numeric()
                ->required()
                ->label('Score')
                ->helperText('Enter the score for the user in this tournament.'),
            TextInput::make('add_to_score')
                ->numeric()
                ->required()
                ->label('Add to Score')
                ->helperText('Enter a value to add to the current score. Use negative values to subtract.'),
        ];

        if ($tournament->is_team_based) {
            $schema[] = TextInput::make('team_name')
                ->label('Team Name')
                ->helperText('Enter a custom team name or leave empty for auto-assignment.');
        }

        return $form->schema($schema);
    }
}
