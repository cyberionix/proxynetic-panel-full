<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('invoices', 'real_total')) {
                $table->decimal('real_total', 12, 2)->nullable();
            }
            if (! Schema::hasColumn('invoices', 'coupon_code_id')) {
                $table->foreignId('coupon_code_id')->nullable()->constrained('coupon_codes')->nullOnDelete();
            }
            if (! Schema::hasColumn('invoices', 'coupon_code_text')) {
                $table->string('coupon_code_text')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach (['coupon_code_text', 'real_total', 'discount_amount'] as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'coupon_code_id')) {
                $table->dropForeign(['coupon_code_id']);
                $table->dropColumn('coupon_code_id');
            }
        });
    }
};
