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
        Schema::create('beverages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('contains_alcohol');
            $table->string('image', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beverages');
    }
};
