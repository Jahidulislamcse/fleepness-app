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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->text('photo');
            $table->text('photo_alt')->nullable();
            $table->text('title')->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('tag_id')->nullable();
            $table->text('description')->nullable();
            $table->text('btn_name')->nullable();
            $table->text('btn_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
