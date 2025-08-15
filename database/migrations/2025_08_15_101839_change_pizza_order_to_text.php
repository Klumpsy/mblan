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
        Schema::table('signups', function (Blueprint $table) {
            $table->text('pizza_order')->nullable()->change(); // Changed from boolean to text
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropColumn([
                'pizza_order',
            ]);
        });
    }
};
