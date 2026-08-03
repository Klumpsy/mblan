<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deelnemers per editie: wie was erbij, welk jaar. Gevuld door bevestigde
 * aanmeldingen (observer) en handmatig via het beheer, zodat ook oude
 * edities gebackfilled kunnen worden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edition_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['edition_id', 'user_id']);
        });

        // Everyone with a confirmed signup was there.
        $rows = DB::table('signups')
            ->where('confirmed', true)
            ->whereNotNull('edition_id')
            ->distinct()
            ->get(['edition_id', 'user_id']);

        foreach ($rows as $row) {
            DB::table('edition_user')->insertOrIgnore([
                'edition_id' => $row->edition_id,
                'user_id' => $row->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('edition_user');
    }
};
