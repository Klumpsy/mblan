<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rules',
        'is_active',
        'time_start',
        'time_end',
        'game_id',
        'schedule_id',
        'is_team_based',
        'scoring_type',
        'score_label',
        'higher_is_better',
        'concluded',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_team_based' => 'boolean',
        'higher_is_better' => 'boolean',
        'concluded' => 'boolean',
    ];

    /**
     * Scoring presets available when creating a tournament. Each preset seeds a
     * sensible score label and sort direction; admins can still override both,
     * so any point-scoring scheme is possible.
     *
     * @return array<string, array{label: string, score_label: string, higher_is_better: bool}>
     */
    public static function scoringPresets(): array
    {
        return [
            'points' => ['label' => 'Punten (hoogste wint)', 'score_label' => 'Punten', 'higher_is_better' => true],
            'kills' => ['label' => 'Kills / Frags', 'score_label' => 'Kills', 'higher_is_better' => true],
            'goals' => ['label' => 'Goals / Doelpunten', 'score_label' => 'Goals', 'higher_is_better' => true],
            'wins' => ['label' => 'Overwinningen', 'score_label' => 'Wins', 'higher_is_better' => true],
            'rounds' => ['label' => 'Gewonnen rondes', 'score_label' => 'Rondes', 'higher_is_better' => true],
            'time' => ['label' => 'Tijd (laagste wint)', 'score_label' => 'Seconden', 'higher_is_better' => false],
            'penalty' => ['label' => 'Strafpunten (laagste wint)', 'score_label' => 'Strafpunten', 'higher_is_better' => false],
            'custom' => ['label' => 'Aangepast', 'score_label' => 'Punten', 'higher_is_better' => true],
        ];
    }

    public function scoreLabel(): string
    {
        return $this->score_label ?: 'Punten';
    }

    /**
     * Time-based tournaments store their score as milliseconds and are edited
     * with minutes/seconds/milliseconds inputs (lowest time wins).
     */
    public function isTimeBased(): bool
    {
        return $this->scoring_type === 'time';
    }

    protected function sortDirection(): string
    {
        return $this->higher_is_better ? 'desc' : 'asc';
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Players who signed up for this tournament. Separate from usersWithScores:
     * registering only reserves a spot, scoring happens on the ladder pivot.
     */
    public function registrations(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tournament_registrations')
            ->withTimestamps();
    }

    public function isRegistered(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->registrations()->whereKey($user->id)->exists();
    }

    /**
     * Everyone who signed up belongs in the game: put registered players who
     * are not on the scoreboard yet on it with a zero score, so team making,
     * scoring and rounds always see the whole field. Idempotent; the admin
     * tabs call it on every load. Returns how many players were added.
     */
    public function putRegistrationsOnScoreboard(): int
    {
        $missing = $this->registrations()
            ->whereNotIn('users.id', $this->usersWithScores()->pluck('users.id'))
            ->pluck('users.id');

        foreach ($missing as $userId) {
            $this->usersWithScores()->attach($userId, ['score' => 0]);
        }

        return $missing->count();
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(TournamentRound::class)->orderBy('number');
    }

    public function nextRoundNumber(): int
    {
        return ((int) $this->rounds()->max('number')) + 1;
    }

    /**
     * Once a tournament has rounds, the rounds are the source of truth:
     * every player's total is the sum of their round points, and the
     * rankings follow from those totals.
     */
    public function applyRoundTotals(): void
    {
        $totals = TournamentRoundScore::query()
            ->join('tournament_rounds', 'tournament_rounds.id', '=', 'tournament_round_scores.tournament_round_id')
            ->where('tournament_rounds.tournament_id', $this->id)
            ->groupBy('tournament_round_scores.user_id')
            ->selectRaw('tournament_round_scores.user_id, SUM(tournament_round_scores.points) as total')
            ->pluck('total', 'tournament_round_scores.user_id');

        foreach ($this->usersWithScores()->pluck('users.id') as $userId) {
            $total = (int) ($totals[$userId] ?? 0);

            $this->usersWithScores()->updateExistingPivot($userId, array_merge(
                ['score' => $total],
                $this->is_team_based ? ['team_score' => $total] : [],
            ));
        }

        $this->recalculateRankings();
    }

    /**
     * Recalculate the ranking column for team and individual tournaments
     * alike. Team tournaments rank whole teams; teamless players sink to
     * the bottom.
     */
    public function recalculateRankings(): void
    {
        if (! $this->is_team_based) {
            $this->updateRankings();

            return;
        }

        $teamScores = \Illuminate\Support\Facades\DB::table('tournament_user')
            ->select('team_number', \Illuminate\Support\Facades\DB::raw('MAX(team_score) as total_score'))
            ->where('tournament_id', $this->id)
            ->whereNotNull('team_number')
            ->groupBy('team_number')
            ->orderBy('total_score', $this->higher_is_better ? 'desc' : 'asc')
            ->orderBy('team_number', 'asc')
            ->get();

        // Dense ranking, exactly like the individual ranking: teams with the
        // same score share a rank and the next team gets the next number.
        $rank = 0;
        $lastScore = null;

        foreach ($teamScores as $team) {
            $score = (int) $team->total_score;

            if ($lastScore === null || $score !== $lastScore) {
                $rank++;
            }

            \Illuminate\Support\Facades\DB::table('tournament_user')
                ->where('tournament_id', $this->id)
                ->where('team_number', $team->team_number)
                ->update(['ranking' => $rank]);

            $lastScore = $score;
        }
    }

    public function usersWithScores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tournament_user')
            ->using(UserTournament::class)
            ->withPivot(
                'score',
                'ranking',
                'team_name',
                'team_number',
                'team_score'
            )
            ->withTimestamps();
    }

    /**
     * Dense ranking: players with the same score share a rank and the next
     * score simply gets the next number (1, 1, 2), so the podium always
     * shows steps 1, 2 and 3.
     */
    public function updateRankings(): void
    {
        $users = $this->usersWithScores()
            ->withPivot('score', 'ranking')
            ->orderByPivot('score', $this->sortDirection())
            ->get();

        $rank = 0;
        $lastScore = null;

        foreach ($users as $user) {
            $score = (int) $user->pivot->score;

            if ($lastScore === null || $score !== $lastScore) {
                $rank++;
            }

            $this->usersWithScores()->updateExistingPivot($user->id, [
                'ranking' => $rank,
            ]);

            $lastScore = $score;
        }
    }

    public function updateUserScore(int $userId, int $score): void
    {
        $this->usersWithScores()->updateExistingPivot($userId, ['score' => $score]);
        $this->updateRankings();
    }

    public function hasYetToStart(): bool
    {
        if (!$this->schedule?->date) {
            return false;
        }

        return now()->lt(Carbon::parse("{$this->schedule->date} {$this->time_start}"));
    }

    public function getLeaderboard(): Collection
    {
        return $this->usersWithScores()
            ->withPivot('score', 'ranking', 'team_name', 'team_number', 'team_score')
            ->orderByRaw('CASE WHEN tournament_user.ranking IS NULL THEN 1 ELSE 0 END')
            ->orderBy('pivot_ranking')
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'score' => $user->pivot->score,
                    'ranking' => $user->pivot->ranking,
                    'team_name' => $user->pivot->team_name,
                    'team_number' => $user->pivot->team_number,
                    'team_score' => $user->pivot->team_score,
                    'profile_photo_path' => $user->profile_photo_path ?? null,
                ];
            });
    }
}
