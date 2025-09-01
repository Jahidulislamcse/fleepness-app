<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlacementTypeAndCatIndexToSectionsTable extends Migration
{
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->enum('placement_type', ['category', 'global', 'all_only'])
                  ->after('bio');

            $table->integer('cat_index')
                  ->nullable()
                  ->after('placement_type');
        });
    }

    public function down()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn(['placement_type', 'cat_index']);
        });
    }
}
