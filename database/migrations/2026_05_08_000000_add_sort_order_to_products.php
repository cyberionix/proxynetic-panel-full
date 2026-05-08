<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'sort_order')) return;
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('is_active')->index();
        });
        DB::statement("UPDATE products SET sort_order = id WHERE sort_order = 0");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'sort_order')) return;
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
