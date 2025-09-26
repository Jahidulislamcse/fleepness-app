<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_order_id')->constrained('seller_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->string('size')->nullable()->nullable(); // optional variant

            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable(); // price * quantity

            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_order_items');
    }
};
