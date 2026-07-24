<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('author_id')->nullable();
            $table->string('image')->nullable();
            $table->longText('content');
            $table->text('preview_text')->nullable();
            $table->string('slug')->unique();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
        });

        // The old blog feature is replaced by news; it was never exposed.
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blogs');
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
