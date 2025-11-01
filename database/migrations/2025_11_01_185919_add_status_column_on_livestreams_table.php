<?php

use App\Constants\LivestreamStatuses;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('livestreams', function (Blueprint $table) {
            $table->string('status')->after('egress_id')->default(LivestreamStatuses::INITIAL);
        });
    }
};
