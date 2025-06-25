<?php

namespace App\Services;

use App\Enums\TshirtSizeType;
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
        bool $joinsBarbecue = false,
        bool $joinsPizza = false,
        bool $isVegan = false,
        bool $wantsTshirt = false,
        ?TshirtSizeType $tshirtSize = null,
        ?string $tshirtText = null

    ): Signup {

        return DB::transaction(function () use (
            $user,
            $edition,
            $schedules,
            $beverages,
            $staysOnCampsite,
            $joinsBarbecue,
            $joinsPizza,
            $isVegan,
            $wantsTshirt,
            $tshirtSize,
            $tshirtText
        ) {
            $signup = Signup::create([
                'user_id' => $user->id,
                'edition_id' => $edition->id,
                'stays_on_campsite' => $staysOnCampsite,
                'joins_barbecue' => $joinsBarbecue,
                'joins_pizza' => $joinsPizza,
                'is_vegan' => $isVegan,
                'wants_tshirt' => $wantsTshirt,
                'tshirt_size' => $tshirtSize,
                'tshirt_text' => $tshirtText,
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
