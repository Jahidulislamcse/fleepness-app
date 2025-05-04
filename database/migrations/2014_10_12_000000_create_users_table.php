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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('shop_name')->nullable();
            $table->unsignedInteger('shop_category')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone_number')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('description')->nullable();
            $table->boolean('payment_bkash')->default(false);
            $table->boolean('payment_nagad')->default(false);
            $table->string('payment_number')->nullable();
            $table->string('role')->default('user');
            $table->string('status')->nullable();
            $table->unsignedInteger('order_count')->nullable();
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('withdrawn_amount', 15, 2)->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
