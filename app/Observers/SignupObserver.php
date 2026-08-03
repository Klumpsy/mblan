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

        $this->attachToEdition($signup);

        AchievementService::check($user, AchievementType::FIRST_SIGNUP->value);
        AchievementService::check($user, AchievementType::JOIN_BARBECUE->value);
        AchievementService::check($user, AchievementType::JOIN_CAMPING->value);
        AchievementService::check($user, AchievementType::GET_TSHIRT_25->value);
    }

    /**
     * Handle the Signup "updated" event.
     */
    public function updated(Signup $signup): void
    {
        if ($signup->isDirty('confirmed') && $signup->confirmed) {
            Mail::to($signup->user->email)->queue(new Approved($signup));
        }

        $this->attachToEdition($signup);

        AchievementService::check($signup->user, AchievementType::DRINK_5_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_10_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_15_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_20_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_24_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_30_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_40_BEERS->value);
        AchievementService::check($signup->user, AchievementType::DRINK_48_BEERS->value);
    }

    /**
     * A confirmed signup means the user attended that edition: record it on
     * the edition_user pivot (the source for recap counts and achievements).
     */
    private function attachToEdition(Signup $signup): void
    {
        if ($signup->confirmed && $signup->edition_id) {
            $signup->user->editions()->syncWithoutDetaching([$signup->edition_id]);
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
