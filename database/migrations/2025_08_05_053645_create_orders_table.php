<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // buyer
            $table->string('order_code')->unique(); // like #ORD123456

            $table->boolean('is_multi_seller')->nullable();
            $table->unsignedInteger('total_sellers')->nullable();

            $table->string('delivery_model')->nullable(); 

            $table->decimal('product_cost', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('platform_fee', 10, 2)->nullable();
            $table->decimal('vat', 10, 2)->nullable();
            $table->decimal('grand_total', 10, 2)->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
