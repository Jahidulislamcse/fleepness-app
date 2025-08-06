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
            $table->boolean('is_multi_seller')->default(false);
            $table->unsignedInteger('total_sellers')->default(1);
            $table->string('delivery_model')->nullable(); // Express, Standard, etc.
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('commission_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
