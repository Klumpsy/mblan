<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_exclusive column to editions table
        Schema::table('editions', function (Blueprint $table) {
            $table->boolean('is_exclusive')->default(false)->after('is_active');
        });

        // Create pivot table for exclusive users
        Schema::create('edition_user_exclusive', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Ensure unique combination
            $table->unique(['edition_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edition_user_exclusive');

        Schema::table('editions', function (Blueprint $table) {
            $table->dropColumn('is_exclusive');
        });
    }
};
