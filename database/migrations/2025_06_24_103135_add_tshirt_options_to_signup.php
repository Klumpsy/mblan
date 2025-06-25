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
            $table->boolean('wants_tshirt')->default(false);
            $table->string('tshirt_size')->nullable();
            $table->string('tshirt_text')->nullable();
            $table->boolean('is_vegan')->default(false);
            $table->boolean('joins_pizza')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signups', function (Blueprint $table) {
            $table->dropColumn([
                'wants_tshirt',
                'tshirt_size',
                'tshirt_text',
                'is_vegan',
                'joins_pizza'
            ]);
        });
    }
};
