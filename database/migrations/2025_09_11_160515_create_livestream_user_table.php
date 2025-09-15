<?php

use App\Models\Livestream;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('livestream_user', function (Blueprint $table) {
            $table->foreignIdFor(Livestream::class);
            $table->foreignIdFor(User::class, 'participant_id');
        });
    }


    public function down(): void
    {
        Schema::table('livestream_user', function (Blueprint $table) {
            //
        });
    }
};
