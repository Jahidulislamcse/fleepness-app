<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysToRestrictOnProductsTable extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['size_template_id']);

            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->onDelete('restrict');

            $table->foreign('size_template_id')
                ->references('id')->on('size_templates')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['size_template_id']);

            $table->foreign('category_id')
                ->references('id')->on('categories')
                ->onDelete('cascade');

            $table->foreign('size_template_id')
                ->references('id')->on('size_templates')
                ->onDelete('cascade');
        });
    }
}
