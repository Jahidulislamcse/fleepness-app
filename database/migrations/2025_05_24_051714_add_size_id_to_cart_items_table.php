<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSizeIdToCartItemsTable extends Migration
{
    public function up()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('size_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['size_id']);
            $table->dropColumn('size_id');
        });
    }
}
