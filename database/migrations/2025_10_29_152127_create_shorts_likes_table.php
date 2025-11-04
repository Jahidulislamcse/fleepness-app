<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shorts_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('short_video_id')->constrained('short_videos')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'short_video_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shorts_likes');
    }
};

