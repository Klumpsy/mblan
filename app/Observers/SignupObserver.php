<?php

namespace App\Observers;

use App\Enums\AchievementType;
use App\Mail\Approved;
use App\Models\Signup;
use App\Services\AchievementService;
use Illuminate\Support\Facades\Mail;

class SignupObserver
{
    /**
     * Handle the Signup "created" event.
     */
    public function created(Signup $signup): void
    {
        $user = $signup->user;
        AchievementService::check($user, AchievementType::FIRST_SIGNUP->value);
        AchievementService::check($user, AchievementType::JOIN_BARBECUE->value);
        AchievementService::check($user, AchievementType::JOIN_CAMPING->value);
    }

    /**
     * Handle the Signup "updated" event.
     */
    public function updated(Signup $signup): void
    {
        if ($signup->isDirty('confirmed') && $signup->confirmed) {
            Mail::to($signup->user->email)->queue(new Approved($signup));
        }
    }

    /**
     * Handle the Signup "deleted" event.
     */
    public function deleted(Signup $signup): void
    {
        //
    }

    /**
     * Handle the Signup "restored" event.
     */
    public function restored(Signup $signup): void
    {
        //
    }

    /**
     * Handle the Signup "force deleted" event.
     */
    public function forceDeleted(Signup $signup): void
    {
        //
    }
}
