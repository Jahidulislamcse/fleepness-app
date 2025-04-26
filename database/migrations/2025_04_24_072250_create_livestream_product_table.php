<?php

use App\Models\Livestream;
use App\Models\Product;
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
        Schema::create('livestream_product', function (Blueprint $table) {
            $table->id(); // Optional but recommended for tracking
            $table->foreignIdFor(Product::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(Livestream::class)->constrained()->onDelete('cascade');

            $table->unique(['product_id', 'livestream_id']);
            $table->timestamps(); // Needed if you use ->withTimestamps() in model
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestream_product');
    }
};
