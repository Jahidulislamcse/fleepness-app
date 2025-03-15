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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('store_title')->nullable();
            $table->string('profile_img')->nullable();
            $table->string('cover_img')->nullable();
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('order')->nullable();
            $table->text('image')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade'); // Self-referencing

            $table->unique(['name', 'parent_id']);

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
