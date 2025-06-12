<?php

namespace App\Observers;

use App\Mail\Approved;
use App\Models\Signup;
use Illuminate\Support\Facades\Mail;

class SignupObserver
{
    /**
     * Handle the Signup "created" event.
     */
    public function created(Signup $signup): void
    {
        //
    }

    /**
     * Handle the Signup "updated" event.
     */
    public function updated(Signup $signup): void
    {
        if ($signup->isDirty('confirmed') && $signup->confirmed) {
            Mail::to($signup->user->email)->send(new Approved($signup));
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
