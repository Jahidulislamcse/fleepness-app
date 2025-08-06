<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryModelsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_models', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->integer('minutes');
            $table->decimal('fee', 10, 2);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_models');
    }
}
