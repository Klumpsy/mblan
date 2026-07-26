<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pizza order round: admins open one, everyone picks their pizza, and the
     * admin sees a single list of who ordered what.
     */
    public function up(): void
    {
        Schema::create('pizza_rounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pizza_rounds');
    }
};
