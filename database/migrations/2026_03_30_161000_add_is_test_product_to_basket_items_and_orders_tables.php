<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('basket_items', function (Blueprint $table) {
            if (! Schema::hasColumn('basket_items', 'is_test_product')) {
                $table->boolean('is_test_product')->default(false);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'is_test_product')) {
                $table->boolean('is_test_product')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('basket_items', function (Blueprint $table) {
            if (Schema::hasColumn('basket_items', 'is_test_product')) {
                $table->dropColumn('is_test_product');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'is_test_product')) {
                $table->dropColumn('is_test_product');
            }
        });
    }
};
