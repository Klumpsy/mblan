<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(false);
            $table->string('primary_color')->default('#65E59A');
            $table->json('palette')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('tagline')->nullable();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->timestamps();
        });

        // MBLAN26 is the running edition; its palette is pinned to the exact
        // handcrafted Forge Green values so nothing on the live site shifts.
        DB::table('editions')->insert([
            'name' => 'MBLAN26',
            'year' => 2026,
            'slug' => 'mblan26',
            'is_active' => true,
            'primary_color' => '#65E59A',
            'palette' => json_encode([
                '50' => '243 253 247', '100' => '230 251 239', '200' => '206 247 223',
                '300' => '175 241 202', '400' => '138 235 178', '500' => '101 229 154',
                '600' => '89 202 136', '700' => '73 165 111', '800' => '57 128 86',
                '900' => '40 92 62', '950' => '26 60 40',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('editions');
    }
};
