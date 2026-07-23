<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Static-password admin accounts are for local/testing only, never production.
        if (app()->environment('local', 'testing')) {
            User::updateOrCreate(
                ['email' => 'bart_klumperman@live.nl'],
                [
                    'name' => 'Bart Klumperman',
                    'password' => Hash::make('admin'),
                    'role' => 'admin',
                    'discord_id' => 'admin-discord',
                    'email_verified_at' => now(),
                ]
            );

            User::updateOrCreate(
                ['email' => 'bart@test.nl'],
                [
                    'name' => 'Bart Test',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'email_verified_at' => now(),
                    'barn_completed' => true,
                    'barn_catches' => 3,
                    'barn_time_ms' => 92000,
                ]
            );
        }

        // A believable crowd. Give roughly half a Discord id so leaderboards populate.
        User::factory()->count(24)->create(['role' => 'user'])
            ->each(function (User $user, int $i) {
                if ($i % 2 === 0) {
                    $user->forceFill(['discord_id' => 'discord-' . $user->id])->save();
                }
            });

        // Seed some "Arti Game" scores so the leaderboard is populated.
        User::inRandomOrder()->take(12)->get()->each(function (User $user) {
            $user->forceFill([
                'barn_completed' => true,
                'barn_catches' => random_int(0, 9),
                'barn_time_ms' => random_int(48, 300) * 1000,
            ])->save();
        });

        $this->call([
            GameSeeder::class,
            ScheduleSeeder::class,
            BlogSeeder::class,
            TournamentSeeder::class,
            SignupSeeder::class,
            AchievementSeeder::class,
            BlogCommentSeeder::class,
        ]);
    }
}
