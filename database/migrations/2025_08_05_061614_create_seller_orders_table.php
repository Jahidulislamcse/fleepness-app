<?php

use App\Enums\SellerOrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Illuminate\Support\enum_value;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');

            $table->enum('status', array_column(SellerOrderStatus::cases(), 'value'))->default(enum_value(SellerOrderStatus::Pending))->nullable();
            $table->text('status_message')->nullable();

            $table->time('delivery_start_time')->nullable();
            $table->time('delivery_end_time')->nullable();

            $table->decimal('product_cost', 10, 2)->nullable();
            $table->decimal('commission', 10, 2)->nullable();
            $table->decimal('balance', 10, 2)->nullable();

            $table->boolean('rider_assigned')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_orders');
    }
};
