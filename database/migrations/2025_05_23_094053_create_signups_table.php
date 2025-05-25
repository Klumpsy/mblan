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
        Schema::create('signups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('edition_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->boolean('stays_on_campsite');
            $table->boolean('joins_barbecue');
            $table->boolean('confirmed');
            $table->unique(['user_id', 'edition_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signups');
    }
};
