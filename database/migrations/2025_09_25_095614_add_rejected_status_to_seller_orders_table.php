<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE seller_orders MODIFY COLUMN status ENUM('pending','packaging','on_the_way','delivered','delayed','rejected') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE seller_orders MODIFY COLUMN status ENUM('pending','packaging','on_the_way','delivered','delayed') NULL");
    }
};
