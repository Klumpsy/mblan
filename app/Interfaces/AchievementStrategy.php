<?php

namespace App\Interfaces;

use App\Models\User;
use App\Models\Achievement;

interface AchievementStrategy
{
    public function handle(User $user, Achievement $achievement): void;
}
