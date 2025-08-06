<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionsTable extends Migration
{
    public function up()
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_name')->nullable();
            $table->string('section_type')->nullable();
            $table->string('section_title')->nullable();
            $table->foreignId('category_id')->constrained('categories');
            $table->integer('index')->nullable();
            $table->boolean('visibility')->default(true);
            $table->string('background_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->timestamps();
        });

        Schema::create('section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('sections');
            $table->string('image')->nullable();
            $table->string('title')->nullable();
            $table->string('bio')->nullable();
            $table->string('tag_id')->nullable();
            $table->integer('index')->nullable();
            $table->boolean('visibility')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('section_items');
        Schema::dropIfExists('sections');
    }
}
