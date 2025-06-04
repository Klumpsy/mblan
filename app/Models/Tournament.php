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
        'is_active',
        'time_start',
        'time_end',
        'game_id',
        'schedule_id',
    ];

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
        return $this->belongsToMany(User::class, 'tournament_user_pivot')
            ->withPivot('score', 'ranking')
            ->withTimestamps();
    }

    public function updateRankings(): void
    {
        $users = $this->usersWithScores()
            ->withPivot('score', 'ranking')
            ->orderByDesc('pivot_score')
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
            ->withPivot('score', 'ranking')
            ->orderBy('pivot_ranking')
            ->get()
            ->map(function ($user) {
                return [
                    'name' => $user->name,
                    'score' => $user->pivot->score,
                    'ranking' => $user->pivot->ranking,
                    'profile_photo_path' => $user->profile_photo_path ?? null,
                ];
            });
    }
}
