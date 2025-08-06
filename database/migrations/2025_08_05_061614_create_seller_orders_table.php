<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            $table->enum('delivery_status', ['pending', 'packaging', 'on_the_way', 'delivered', 'delayed'])->default('pending');
            $table->text('delivery_message')->nullable();

            $table->time('delivery_start_time')->nullable();
            $table->time('delivery_end_time')->nullable();

            $table->decimal('product_total', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);

            $table->boolean('rider_assigned')->default(false);

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_orders');
    }
};
