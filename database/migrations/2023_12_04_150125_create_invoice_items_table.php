<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
            $table->id();
            $table->enum("type", ["NEW", "RENEW", "UPGRADE", "BALANCE", "ADDITIONAL_QUOTA", "ADDITIONAL_QUOTA_DURATION"])->default("NEW");
            $table->string("name");
            $table->decimal("total_price")->default(0.00);
            $table->unsignedTinyInteger("vat_percent")->default(0);
            $table->decimal("total_price_with_vat")->default(0.00);
            $table->json("additional_services")->nullable();

            $table->foreignId('product_id')->nullable();
            $table->foreignId('price_id')->nullable();
            $table->foreignId('order_id')->nullable();
            $table->foreignId('order_detail_id')->nullable();
            $table->foreignId("invoice_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
