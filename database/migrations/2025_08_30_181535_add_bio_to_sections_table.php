<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBioToSectionsTable extends Migration
{
    public function up()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->text('bio')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->dropColumn('bio'); 
        });
    }
}
