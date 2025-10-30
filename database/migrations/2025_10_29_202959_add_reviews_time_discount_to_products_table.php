<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('reviews')->nullable()->after('long_description');
            $table->string('time')->nullable()->after('reviews');
            $table->decimal('discount', 10, 2)->nullable()->after('time');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['reviews', 'time', 'discount']);
        });
    }
};
