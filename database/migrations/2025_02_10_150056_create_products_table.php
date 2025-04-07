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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->json('tags')->nullable();
            $table->foreignId('size_template_id')->constrained('categories')->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->unsignedInteger('quantity')->nullable();
            $table->unsignedInteger('order_count')->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->text('status')->nullable();
            $table->text('admin_approval')->default('approved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
