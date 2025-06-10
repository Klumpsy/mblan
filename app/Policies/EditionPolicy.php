<?php

namespace App\Policies;

use App\Models\Edition;
use App\Models\User;

class EditionPolicy
{
    public function signup(User $user, Edition $edition): bool
    {
        return !$edition->signups()->where('user_id', $user->id)->exists();
    }

    public function signout(User $user, Edition $edition): bool
    {
        return $edition->signups()->where('user_id', $user->id)->exists();
    }
}
