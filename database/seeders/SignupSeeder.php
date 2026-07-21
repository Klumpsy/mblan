<?php

namespace Database\Seeders;

use App\Models\Edition;
use App\Models\Signup;
use App\Models\User;
use Illuminate\Database\Seeder;

class SignupSeeder extends Seeder
{
    public function run(): void
    {
        $mblan25 = Edition::where('slug', 'mblan25')->first();
        $mblan26 = Edition::where('slug', 'mblan26')->first();

        // Past edition: a full house, all confirmed.
        $this->attachSignups($mblan25, User::inRandomOrder()->take(20)->get(), confirmed: true, withBeers: false);

        // Active edition: confirmed signups with beer counts to feed the leaderboard.
        $this->attachSignups($mblan26, User::inRandomOrder()->take(18)->get(), confirmed: true, withBeers: true);
    }

    private function attachSignups(?Edition $edition, $users, bool $confirmed, bool $withBeers): void
    {
        if (!$edition) {
            return;
        }

        foreach ($users as $user) {
            // The beer leaderboard only counts users with a Discord id.
            if ($withBeers && !$user->discord_id) {
                $user->forceFill(['discord_id' => 'seed-' . $user->id])->save();
            }

            $beers = $withBeers ? random_int(0, 32) : 0;

            Signup::updateOrCreate(
                ['user_id' => $user->id, 'edition_id' => $edition->id],
                [
                    'stays_on_campsite' => (bool) random_int(0, 1),
                    'joins_barbecue' => (bool) random_int(0, 1),
                    'confirmed' => $confirmed,
                    'beer_count' => $beers,
                    'last_beer_at' => $beers > 0 ? now()->subMinutes(random_int(1, 600)) : null,
                    'wants_tshirt' => true,
                    'tshirt_size' => ['S', 'M', 'L', 'XL'][random_int(0, 3)],
                    'tshirt_text' => $edition->name,
                    'is_vegan' => (bool) random_int(0, 4) === 0,
                    'joins_pizza' => (bool) random_int(0, 9) !== 0,
                    'has_paid' => $confirmed,
                ]
            );
        }
    }
}
