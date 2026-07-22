<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function updateRankings(): void
    {
        $users = $this->usersWithScores()
            ->withPivot('score', 'ranking')
            ->orderByPivot('score', $this->sortDirection())
            ->get();

        $rank = 1;
        $lastScore = null;
        $actualRank = 1;

        foreach ($users as $index => $user) {
            $score = $user->pivot->score;

            if ($score !== $lastScore) {
                $rank = $actualRank;
            }

            $this->usersWithScores()->updateExistingPivot($user->id, [
                'ranking' => $rank,
            ]);

            $lastScore = $score;
            $actualRank++;
        }
    }

    public function updateUserScore(int $userId, int $score): void
    {
        $this->usersWithScores()->updateExistingPivot($userId, ['score' => $score]);
        $this->updateRankings();
    }

    public function hasYetToStart(): bool
    {
        $start = Carbon::parse("{$this->schedule->date} {$this->time_start}");

        return (
            (int)$this->schedule->edition->year === now()->year &&
            now()->lt($start)
        );
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
