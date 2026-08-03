<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The LANs before the site existed: MBLAN24 and MBLAN25 as clean, archived
 * editions. They start empty; schedules, tournaments and photos can be
 * backfilled through the admin panel, where every resource has an edition
 * field and filter.
 */
return new class extends Migration
{
    private array $editions = [
        ['name' => 'MBLAN24', 'year' => 2024, 'slug' => 'mblan24', 'primary_color' => '#7f9fe5'],
        ['name' => 'MBLAN25', 'year' => 2025, 'slug' => 'mblan25', 'primary_color' => '#e5a54a'],
    ];

    public function up(): void
    {
        foreach ($this->editions as $edition) {
            if (DB::table('editions')->where('slug', $edition['slug'])->exists()) {
                continue;
            }

            DB::table('editions')->insert($edition + [
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('editions')
            ->whereIn('slug', array_column($this->editions, 'slug'))
            ->delete();
    }
};
