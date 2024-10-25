<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('flashcard_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flashcard_id');
            $table->unsignedBigInteger('user_id');
            $table->string('selected_answer');
            $table->boolean('is_correct');
            $table->timestamps();

            $table->foreign('flashcard_id')->references('id')->on('flashcards')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unique(['flashcard_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('flashcard_responses');
    }
};
