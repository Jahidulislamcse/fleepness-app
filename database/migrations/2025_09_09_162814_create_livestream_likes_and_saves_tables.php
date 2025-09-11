<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivestreamLikesAndSavesTables extends Migration
{

    public function up(): void
    {
        Schema::create('livestream_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('livestream_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('livestream_id')->references('id')->on('livestreams')->onDelete('cascade');
            $table->unique(['user_id', 'livestream_id']);
        });

        Schema::create('livestream_saves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('livestream_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('livestream_id')->references('id')->on('livestreams')->onDelete('cascade');
            $table->unique(['user_id', 'livestream_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('livestream_likes');
        Schema::dropIfExists('livestream_saves');
    }
}
