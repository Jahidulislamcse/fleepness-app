<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('short_videos', function (Blueprint $table) {
            $table->unsignedBigInteger('likes_count')->default(0)->after('alt_text');
        });
    }

    public function down(): void
    {
        Schema::table('short_videos', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
