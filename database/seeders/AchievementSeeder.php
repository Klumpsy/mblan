<?php

namespace Database\Seeders;

use App\Models\Achievement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::where('role', 'admin')->first() ?? User::first();

        $achievements = [
            ['name' => 'First Forge', 'description' => 'Attend your very first MBLAN edition.', 'type' => 'manual', 'threshold' => 1],
            ['name' => 'Barn Regular', 'description' => 'Attend three MBLAN editions.', 'type' => 'automatic', 'threshold' => 3],
            ['name' => 'Beer Blacksmith', 'description' => 'Log 25 beers during a single edition.', 'type' => 'automatic', 'threshold' => 25],
            ['name' => 'Tournament Victor', 'description' => 'Win any official tournament bracket.', 'type' => 'manual', 'threshold' => 1],
            ['name' => 'Night Owl', 'description' => 'Still fragging at 4 AM.', 'type' => 'manual', 'threshold' => 1],
            ['name' => 'Pizza Devourer', 'description' => 'Order the legendary XL pizza.', 'type' => 'automatic', 'threshold' => 1],
        ];

        foreach ($achievements as $data) {
            $achievement = Achievement::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, [
                    'slug' => Str::slug($data['name']),
                    'icon_path' => null,
                    'color' => '#65E59A',
                    'grayed_color' => '#3a4a42',
                    'model_type' => null,
                    'created_by' => $creator?->id,
                ])
            );

            // Award a realistic spread: some users unlocked, some in progress.
            $users = User::inRandomOrder()->take(random_int(6, 14))->get();
            foreach ($users as $user) {
                $unlocked = fake()->boolean(55);
                $achievement->users()->syncWithoutDetaching([
                    $user->id => [
                        'progress' => $unlocked
                            ? $data['threshold']
                            : random_int(0, max(0, (int) $data['threshold'] - 1)),
                        'achieved_at' => $unlocked ? now()->subDays(random_int(1, 400)) : null,
                    ],
                ]);
            }
        }
    }
}
