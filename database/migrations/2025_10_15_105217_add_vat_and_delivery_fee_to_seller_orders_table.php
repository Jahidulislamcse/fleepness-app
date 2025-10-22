<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->decimal('vat', 10, 2)->nullable()->after('commission');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('vat');
        });
    }

    public function down(): void
    {
        Schema::table('seller_orders', function (Blueprint $table) {
            $table->dropColumn(['vat', 'delivery_fee']);
        });
    }
};
