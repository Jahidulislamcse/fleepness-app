<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->text('logo');
            $table->text('favicon');
            $table->text('address');
            $table->text('phone');
            $table->text('email');
            $table->text('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('footer_logo');
            $table->text('footer_text');
            $table->text('footer_copyright_by');
            $table->text('footer_copyright_url');
            $table->text('footer_bg_image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
