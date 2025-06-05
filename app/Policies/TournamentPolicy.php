<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;

class TournamentPolicy
{
    public function view(User $user, Tournament $tournament): bool
    {
        return !$tournament->edition->signups()->where('user_id', $user->id)->exists();
    }
}
