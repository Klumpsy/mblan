<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Edition;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Bart Klumpers',
            'email' => 'bart_klumperman@live.nl',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ]);

        $users = User::factory()->count(3)->create([
            'role' => 'user',
        ]);

        $this->call([
            GameSeeder::class,
            EditionSeeder::class,
            ScheduleSeeder::class,
        ]);

        $mblan24 = Edition::where('slug', 'mblan24')->first();
        $mblan25 = Edition::where('slug', 'mblan25')->first();
    }
}
