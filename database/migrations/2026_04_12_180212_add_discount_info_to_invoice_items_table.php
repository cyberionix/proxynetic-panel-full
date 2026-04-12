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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->nullable()->after('total_price_with_vat');
            $table->string('discount_coupon_text')->nullable()->after('discount_percent');
            $table->decimal('original_price_with_vat', 10, 2)->nullable()->after('discount_coupon_text');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_coupon_text', 'original_price_with_vat']);
        });
    }
};
