<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One order per user per round: the chosen pizza plus free-text notes
     * (extra toppings, "geen ui", small/large, ...).
     */
    public function up(): void
    {
        Schema::create('pizza_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pizza_round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pizza');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['pizza_round_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizza_orders');
    }
};
