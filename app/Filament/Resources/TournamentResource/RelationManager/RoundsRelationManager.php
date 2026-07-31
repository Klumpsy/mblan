<?php

namespace App\Filament\Resources\TournamentResource\RelationManager;

use App\Models\TournamentRound;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class RoundsRelationManager extends RelationManager
{
    protected static string $relationship = 'rounds';

    protected static ?string $title = 'Rondes';

    /**
     * One points input per team (team tournaments) or per player, plus an
     * optional round name. Existing points prefill when editing a round.
     *
     * @return array<int, TextInput>
     */
    protected function pointsFields(?TournamentRound $round = null): array
    {
        $tournament = $this->getOwnerRecord();
        $existing = $round
            ? $round->scores()->pluck('points', 'user_id')
            : collect();

        $fields = [
            TextInput::make('name')
                ->label('Naam (optioneel)')
                ->placeholder('Ronde ' . ($round?->number ?? $tournament->nextRoundNumber()))
                ->default($round?->name),
        ];

        $label = $tournament->scoreLabel();

        if ($tournament->is_team_based) {
            $teams = DB::table('tournament_user')
                ->where('tournament_id', $tournament->id)
                ->whereNotNull('team_number')
                ->orderBy('team_number')
                ->get(['user_id', 'team_number', 'team_name'])
                ->groupBy('team_number');

            foreach ($teams as $number => $members) {
                $fields[] = TextInput::make("team_points.{$number}")
                    ->label(($members->first()->team_name ?: "Team {$number}") . " · {$label}")
                    ->numeric()
                    ->default((int) ($existing[$members->first()->user_id] ?? 0))
                    ->required();
            }
        } else {
            $players = $tournament->usersWithScores()->orderBy('name')->get();

            foreach ($players as $player) {
                $fields[] = TextInput::make("player_points.{$player->id}")
                    ->label("{$player->name} · {$label}")
                    ->numeric()
                    ->default((int) ($existing[$player->id] ?? 0))
                    ->required();
            }
        }

        return $fields;
    }

    /**
     * Store the submitted points on the round (team points fan out to every
     * team member, mirroring how the scoreboard propagates team scores) and
     * rebuild the totals + ranking from all rounds.
     */
    protected function saveRound(TournamentRound $round, array $data): void
    {
        $tournament = $this->getOwnerRecord();

        $pointsByUser = [];

        if ($tournament->is_team_based) {
            $members = DB::table('tournament_user')
                ->where('tournament_id', $tournament->id)
                ->whereNotNull('team_number')
                ->get(['user_id', 'team_number']);

            foreach ($members as $member) {
                $pointsByUser[(int) $member->user_id] = (int) ($data['team_points'][$member->team_number] ?? 0);
            }
        } else {
            foreach ($data['player_points'] ?? [] as $userId => $points) {
                $pointsByUser[(int) $userId] = (int) $points;
            }
        }

        foreach ($pointsByUser as $userId => $points) {
            $round->scores()->updateOrCreate(['user_id' => $userId], ['points' => $points]);
        }

        $tournament->applyRoundTotals();
    }

    public function table(Table $table): Table
    {
        $tournament = $this->getOwnerRecord();

        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('#')
                    ->badge()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Naam')
                    ->formatStateUsing(fn (?string $state, TournamentRound $record) => $record->label())
                    ->placeholder(fn (TournamentRound $record) => $record->label()),
                TextColumn::make('scores_sum_points')
                    ->label('Totaal ' . strtolower($tournament->scoreLabel()))
                    ->sum('scores', 'points'),
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('number', 'asc')
            ->headerActions([
                Action::make('add_round')
                    ->label('Ronde toevoegen')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->visible(fn () => $tournament->usersWithScores()->exists())
                    ->modalHeading('Ronde toevoegen')
                    ->modalDescription('Vul de behaalde ' . strtolower($tournament->scoreLabel()) . ' van deze ronde in. De totalen en ranking worden automatisch bijgewerkt.')
                    ->form(fn () => $this->pointsFields())
                    ->action(function (array $data): void {
                        $tournament = $this->getOwnerRecord();

                        $round = $tournament->rounds()->create([
                            'number' => $tournament->nextRoundNumber(),
                            'name' => $data['name'] ?? null,
                        ]);

                        $this->saveRound($round, $data);

                        Notification::make()
                            ->title('Ronde opgeslagen')
                            ->body("{$round->label()} is opgeslagen en de totalen zijn bijgewerkt.")
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make('edit_points')
                    ->label('Punten bewerken')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading(fn (TournamentRound $record) => "{$record->label()} bewerken")
                    ->form(fn (TournamentRound $record) => $this->pointsFields($record))
                    ->action(function (array $data, TournamentRound $record): void {
                        $record->update(['name' => $data['name'] ?? null]);

                        $this->saveRound($record, $data);

                        Notification::make()
                            ->title('Ronde bijgewerkt')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('Verwijderen')
                    ->modalDescription('De punten van deze ronde worden van de totalen afgetrokken.')
                    ->after(fn () => $this->getOwnerRecord()->applyRoundTotals()),
            ]);
    }
}
