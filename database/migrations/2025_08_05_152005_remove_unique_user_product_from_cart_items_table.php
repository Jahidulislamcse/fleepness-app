<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveUniqueUserProductFromCartItemsTable extends Migration
{
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // First, drop foreign key constraints
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);

            // Then drop the unique index
            $table->dropUnique('cart_items_user_id_product_id_unique');

            // Re-add the foreign keys without the unique constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            // Drop foreign keys again
            $table->dropForeign(['user_id']);
            $table->dropForeign(['product_id']);

            // Re-apply the unique index
            $table->unique(['user_id', 'product_id']);

            // Restore foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
}
