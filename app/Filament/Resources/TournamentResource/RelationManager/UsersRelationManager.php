<?php

namespace App\Filament\Resources\TournamentResource\RelationManager;

use App\Models\User;
use App\Services\DiscordWebhookService;
use App\Support\TimeScore;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'usersWithScores';

    protected static ?string $title = 'Scores';

    /**
     * Players who may be given a score: those who signed up for this tournament
     * and are not already on the scoreboard. Keyed id => name for a Select.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function eligiblePlayers(): \Illuminate\Support\Collection
    {
        $tournament = $this->getOwnerRecord();
        $attachedUserIds = $tournament->usersWithScores()->pluck('users.id')->toArray();

        return $tournament->registrations()
            ->whereNotIn('users.id', $attachedUserIds)
            ->orderBy('name')
            ->pluck('name', 'users.id');
    }

    /**
     * Set a player's score to an absolute value, propagate it to teammates for
     * team tournaments, and refresh the ranking. Both the "add" and "edit"
     * score actions funnel through here.
     */
    private function setScore($record, int $newScore): void
    {
        $tournament = $this->getOwnerRecord();

        $tournament->usersWithScores()->updateExistingPivot($record->id, array_filter([
            'score' => $newScore,
            'team_score' => $tournament->is_team_based ? $newScore : null,
        ], fn ($value) => $value !== null));

        if ($tournament->is_team_based && ! is_null($record->pivot->team_number)) {
            DB::table('tournament_user')
                ->where('tournament_id', $tournament->id)
                ->where('team_number', $record->pivot->team_number)
                ->update(['score' => $newScore, 'team_score' => $newScore]);
        }

        $this->recalculateRanking();
    }

    /**
     * The form fields for entering a score. Time tournaments get minutes /
     * seconds / milliseconds inputs; everything else gets a single number under
     * $numberField ('amount' for adding, 'score' for setting an absolute total).
     *
     * @return array<int, TextInput>
     */
    private function scoreFields(string $numberField): array
    {
        $tournament = $this->getOwnerRecord();

        if ($tournament->isTimeBased()) {
            return [
                TextInput::make('minutes')->label('Minuten')->numeric()->default(0)->minValue(0)->required(),
                TextInput::make('seconds')->label('Seconden')->numeric()->default(0)->minValue(0)->maxValue(59)->required(),
                TextInput::make('milliseconds')->label('Milliseconden')->numeric()->default(0)->minValue(0)->maxValue(999)->required(),
            ];
        }

        return [
            TextInput::make($numberField)
                ->label($tournament->scoreLabel())
                ->numeric()
                ->required(),
        ];
    }

    /**
     * Read a score (or delta) value out of submitted form data.
     */
    private function scoreFromData(array $data, string $numberField): int
    {
        if ($this->getOwnerRecord()->isTimeBased()) {
            return TimeScore::toMilliseconds(
                (int) ($data['minutes'] ?? 0),
                (int) ($data['seconds'] ?? 0),
                (int) ($data['milliseconds'] ?? 0),
            );
        }

        return (int) ($data[$numberField] ?? 0);
    }

    protected function recalculateRanking(): void
    {
        $tournament = $this->getOwnerRecord();

        // Remember who was on top so we can announce a change of leader below.
        $previousLeaderId = $tournament->usersWithScores()
            ->wherePivot('ranking', 1)
            ->orderByPivot('score', $tournament->higher_is_better ? 'desc' : 'asc')
            ->first()?->id;

        if ($tournament->is_team_based) {
            $teamScores = DB::table('tournament_user')
                ->select('team_number', 'team_name', DB::raw('MAX(team_score) as total_score'))
                ->where('tournament_id', $tournament->id)
                ->whereNotNull('team_number')
                ->groupBy('team_number', 'team_name')
                ->orderBy('total_score', $tournament->higher_is_better ? 'desc' : 'asc')
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
            $direction = $tournament->higher_is_better ? 'desc' : 'asc';
            $users = $tournament->usersWithScores()
                ->orderByPivot('score', $direction)
                ->get();

            foreach ($users as $index => $user) {
                $tournament->usersWithScores()->updateExistingPivot($user->id, [
                    'ranking' => $index + 1,
                ]);
            }
        }

        $this->announceLeaderChange($tournament, $previousLeaderId);
    }

    /**
     * Post to Discord when the number one on the ladder actually changes,
     * so the channel sees the exciting moments without a message per edit.
     */
    protected function announceLeaderChange(\App\Models\Tournament $tournament, ?int $previousLeaderId): void
    {
        $leader = $tournament->usersWithScores()
            ->wherePivot('ranking', 1)
            ->orderByPivot('score', $tournament->higher_is_better ? 'desc' : 'asc')
            ->withPivot('score')
            ->first();

        if (! $leader || $leader->id === $previousLeaderId) {
            return;
        }

        app(\App\Services\DiscordWebhookService::class)
            ->announceLadderLeaderChange($tournament, $leader, (int) $leader->pivot->score);
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
        $scoreLabel = $tournament->scoreLabel();

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
                ->label($tournament->isTimeBased() ? 'Teamtijd' : 'Team Score')
                ->formatStateUsing(fn ($state) => $tournament->isTimeBased() && $state !== null ? TimeScore::format((int) $state) : $state)
                ->sortable(
                    query: fn($query, $direction) =>
                    $query->orderBy('tournament_user.team_score', $direction)
                )
                ->placeholder('-');
        } else {
            $columns[] = TextColumn::make('pivot.score')
                ->label($scoreLabel)
                ->formatStateUsing(fn ($state) => $tournament->isTimeBased() && $state !== null ? TimeScore::format((int) $state) : $state)
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
                        ->label('Speler')
                        // Only players who signed up for this tournament can get a
                        // score, minus anyone already on the scoreboard.
                        ->options(fn () => $this->eligiblePlayers())
                        ->searchable()
                        ->required()
                        ->preload()
                        ->placeholder('Kies een aangemelde speler')
                        ->helperText('Alleen spelers die zich hebben aangemeld voor dit toernooi.'),
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
                ->label('Teams maken')
                ->icon('heroicon-o-user-group')
                ->color('success')
                ->form([
                    TextInput::make('team_size')
                        ->label('Teamgrootte')
                        ->numeric()
                        ->required()
                        ->minValue(2)
                        ->maxValue(10)
                        ->default(2)
                        ->helperText('Aantal spelers per team.'),
                    Toggle::make('post_to_discord')
                        ->label('Teams naar Discord posten')
                        ->helperText('Plaatst de teamindeling meteen in het Discord-kanaal.')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $this->createTeams((int) $data['team_size']);

                    if (! empty($data['post_to_discord'])) {
                        app(DiscordWebhookService::class)->announceTeams($this->getOwnerRecord());
                    }
                })
                ->requiresConfirmation()
                ->modalDescription('Maakt automatisch teams van spelers die nog geen team hebben.');

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
            // Primary, one-field quick add: type the points/seconds from the last
            // round and they are added to the running total. This is the fast path
            // admins use during play — no need to compute the new absolute value.
            Action::make('addScore')
                ->label('Score toevoegen')
                ->modalHeading(fn ($record) => "{$scoreLabel} toevoegen voor {$record->name}")
                ->icon('heroicon-o-plus')
                ->color('success')
                ->form($this->scoreFields('amount'))
                ->action(function (array $data, $record): void {
                    $current = (int) ($record->pivot->score ?? 0);
                    $this->setScore($record, $current + $this->scoreFromData($data, 'amount'));
                    Notification::make()->title('Score bijgewerkt')->success()->send();
                }),

            // Secondary: set the exact total, for corrections.
            EditAction::make()
                ->label('Score bijwerken')
                ->modalHeading('Score bijwerken')
                ->icon('heroicon-o-pencil-square')
                // Prefill the player's current score from the pivot so the admin
                // sees and edits the real value (the score lives on the pivot,
                // not on the user, so the default fill would come up empty).
                ->fillForm(function ($record) use ($tournament) {
                    $base = ['team_name' => $record->pivot->team_name ?? null];

                    if ($tournament->isTimeBased()) {
                        return array_merge($base, TimeScore::toParts((int) ($record->pivot->score ?? 0)));
                    }

                    return array_merge($base, ['score' => $record->pivot->score ?? 0]);
                })
                ->form(array_values(array_filter([
                    ...$this->scoreFields('score'),
                    $tournament->is_team_based
                        ? TextInput::make('team_name')
                            ->label('Teamnaam')
                            ->helperText('Laat leeg voor automatische toewijzing.')
                        : null,
                ])))
                ->action(function (array $data, $record) use ($tournament): void {
                    if ($tournament->is_team_based && array_key_exists('team_name', $data)) {
                        $tournament->usersWithScores()->updateExistingPivot($record->id, ['team_name' => $data['team_name']]);
                    }

                    $this->setScore($record, $this->scoreFromData($data, 'score'));

                    Notification::make()->title('Score bijgewerkt')->success()->send();
                }),

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
}
