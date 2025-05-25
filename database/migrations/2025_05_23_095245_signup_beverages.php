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
        Schema::create('signup_beverages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('signup_id')->constrained()->onDelete('cascade');
            $table->foreignId('beverage_id')->constrained()->onDelete('cascade');
            $table->integer('preference_order')->nullable();
            $table->timestamps();

            $table->unique(['signup_id', 'beverage_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signup_beverages');
    }
};
