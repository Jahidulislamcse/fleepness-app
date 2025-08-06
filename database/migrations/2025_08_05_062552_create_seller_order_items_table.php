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
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 10, 2); // price * quantity

            $table->string('size')->nullable(); // optional variant

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_order_items');
    }
};
