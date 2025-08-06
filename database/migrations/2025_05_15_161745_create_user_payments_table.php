<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('user_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            $table->string('account_number');
            $table->timestamps();

            $table->unique(['user_id', 'payment_method_id']); // user can have one account per method
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_payments');
    }
}
