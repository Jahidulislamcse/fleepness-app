<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddressesTableAddFullAddressFields extends Migration
{
    public function up()
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'street')) {
                $table->dropColumn('street');
            }
            if (Schema::hasColumn('addresses', 'country')) {
                $table->dropColumn('country');
            }

            $table->string('label')->nullable()->change();
            $table->string('postal_code')->nullable()->change();
            $table->string('city')->nullable()->change();

            $table->string('address_text');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('area')->nullable();
            $table->boolean('is_default')->default(false)->after('area');
        });
    }

    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('street')->after('formatted_address');
            $table->string('country')->after('postal_code');

            $table->string('label')->nullable(false)->change();
            $table->string('postal_code')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();

            $table->dropColumn([
                'address_text',
                'address_line_1',
                'address_line_2',
                'area',
                'is_default',
            ]);
        });
    }
}
