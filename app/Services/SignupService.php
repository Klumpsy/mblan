<?php

namespace App\Services;

use App\Models\Edition;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SignupService
{
    public function createSignup(
        User $user,
        Edition $edition,
        array $schedules,
        array $beverages = [],
        bool $staysOnCampsite = false,
        bool $joinsBarbecue = false
    ): Signup {


        return DB::transaction(function () use (
            $user,
            $edition,
            $schedules,
            $beverages,
            $staysOnCampsite,
            $joinsBarbecue
        ) {
            $signup = Signup::create([
                'user_id' => $user->id,
                'edition_id' => $edition->id,
                'stays_on_campsite' => $staysOnCampsite,
                'joins_barbecue' => $joinsBarbecue,
                'confirmed' => false
            ]);

            $signup->schedules()->attach($schedules);

            if (!empty($beverages)) {
                $signup->beverages()->attach($beverages);
            }

            return $signup;
        });
    }
}
