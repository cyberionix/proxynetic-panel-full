<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            if (! Schema::hasColumn('baskets', 'coupon_code_id')) {
                $table->foreignId('coupon_code_id')->nullable()->constrained('coupon_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('baskets', 'coupon_code_text')) {
                $table->string('coupon_code_text')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            if (Schema::hasColumn('baskets', 'coupon_code_text')) {
                $table->dropColumn('coupon_code_text');
            }
        });

        Schema::table('baskets', function (Blueprint $table) {
            if (Schema::hasColumn('baskets', 'coupon_code_id')) {
                $table->dropForeign(['coupon_code_id']);
                $table->dropColumn('coupon_code_id');
            }
        });
    }
};
