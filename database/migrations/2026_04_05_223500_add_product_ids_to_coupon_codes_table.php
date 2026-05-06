<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('coupon_codes', 'product_ids')) {
            Schema::table('coupon_codes', function (Blueprint $table) {
                $table->json('product_ids')->nullable()->after('use_limit');
            });
        }
    }

    public function down(): void
    {
        Schema::table('coupon_codes', function (Blueprint $table) {
            $table->dropColumn('product_ids');
        });
    }
};
